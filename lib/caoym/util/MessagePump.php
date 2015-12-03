<?php
/***************************************************************************
 *
* Copyright (c) 2013 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: MessagePump.php 58821 2015-01-17 03:51:41Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 *         @brief ResetOutput
 */
namespace caoym\util;

/**
 * 处理消息循环
 * 消息执行顺序为先入后出(栈)
 * 消息被分成正常执行消息和空闲执行消息
 * 空闲执行消息只有在没有正常执行消息时才会执行
 * 允许同时存在多个队列,可以关闭某个队列,如果某个队列中所有消息完成,将触发end事件
 *
 * @author caoym
 */
class MessagePump
{

    /**
     * 创建一个新的消息队列
     * @param callable $onEnd 当队列执行完后调用
     * @return int 队列id
     */
    public function newQueue(callable $onEnd = null)
    {
        Verify::isTrue(! isset($this->queues[$this->start_queue_id]));
        $this->action_queues[$this->start_queue_id] = array();
        $this->idle_queues[$this->start_queue_id] = array();
        $this->end_handles[$this->start_queue_id] = $onEnd;
        Logger::debug("[MQ {$this->start_queue_id}] created");
        return $this->start_queue_id++;
    }

    /**
     * 关闭一个消息队列,队列中未执行的操作将不会再执行
     * 
     * @param int $queue_id            
     * @return void
     */
    public function closeQueue($queue_id)
    {
        if (! isset($this->idle_queues[$queue_id])){
            return;
        }
        Logger::debug("[MQ $queue_id] attempt to close");
        // 队列末尾加入null,消息循环处理到null后任务队列结束
        array_unshift($this->action_queues[$queue_id], null);
        $this->next_action[]=$queue_id;
        // $this->idle_queues[$queue_id][]=null;
    }

    /**
     * 加入常规执行消息
     * 
     * @param int $queue_id队列id
     * @param callable $action  调用方法 
     * @param array $args  调用参数
     * @param callable $exception_handle异常处理
     * @param string $desc 描述信息
     * @param boolean immediately 是否立即执行
     * @return void
     */
    public function pushAction($queue_id, $action, $args, $exception_handle, $desc, $immediately=false)
    {
        if (! isset($this->action_queues[$queue_id])) {
            Logger::warning("unknown message queue $queue_id");
            return;
        }
        $count=count($this->action_queues[$queue_id]);
        if($count && $this->action_queues[$queue_id][0] ===null){
            Logger::warning("[MQ $queue_id] try to add action to closed queue");
            return;
        }
        $action = array(
            $action,
            $args,
            $exception_handle,
            $desc,
        );
        if($immediately){
            Logger::debug("[MQ $queue_id] new action [$desc'] immediately");
            $this->callAction($queue_id, $action);
        }else{
            Logger::debug("[MQ $queue_id] new action [$desc']");
            $this->action_queues[$queue_id][] =$action;
            $this->next_action[] = $queue_id;
        }
    }

    /**
     * 加入空闲执行消息
     *
     * @param callable $action            
     * @param callable $exception_handle            
     * @param string $desc
     *            描述信息
     * @return void
     */
    public function pushIdle($queue_id, $action, $args, $exception_handle, $desc)
    {
        if (! isset($this->idle_queues[$queue_id])) {
            Logger::warning("unknown message queue $queue_id");
            return;
        }
        $this->idle_queues[$queue_id][] = array(
            $action,
            $args,
            $exception_handle,
            $desc,
        );
        $this->next_idle[] = $queue_id;
    }

    /**
     * 运行消息循环
     * @return void
     */
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        Logger::debug("[MQ Pump] begin");
        $this->is_running = true;
        while (count($this->next_action) || count($this->next_idle)) {
            //没有活动事件,执行idle事件
            if(count($this->next_action) === 0){
                $queue_id = array_pop($this->next_idle);
                if (!isset($this->idle_queues[$queue_id])) { // 队列可能被关闭
                    continue;
                }
                $idle = array_pop($this->idle_queues[$queue_id]);
                Verify::isTrue($idle !== null, 'never been here!!');
                if ($idle[2] !== null) {
                    try {
                        call_user_func_array($idle[0], $idle[1]);
                    } catch (\Exception $e) {
                        $idle[2]($e);
                    }
                } else {
                    call_user_func_array($idle[0],$idle[1]);
                }
                continue;
            }
            $queue_id = array_pop($this->next_action);
            if (!isset($this->action_queues[$queue_id])) { // 队列可能被关闭
                continue;
            }
            $actions = &$this->action_queues[$queue_id];
            $left = count($actions);
            $action = array_pop($actions);
            if ($action !== null) {
                $this->callAction($queue_id, $action);
            } elseif ($left !== 0) { 
                //null插入队列,表示执行队列结束, 可以安全关闭队列了
                $onend = $this->end_handles[$queue_id];
                unset($this->action_queues[$queue_id]);
                unset($this->idle_queues[$queue_id]);
                unset($this->end_handles[$queue_id]);
                Logger::debug("[MQ $queue_id] closed");
                if ($onend !== null) {
                    $onend();
                }
            }
           
        }
        //不是在单个队列为空时将其关闭,因为对于存在子流程的时候,其消息队列可能为空,但
        //其他流程执行可能导致子流程产生活动消息,所以不能在队列为空时就关闭队列
        foreach ($this->end_handles as $onend){
            if($onend !== null){
                $onend();
            }
        }
        $this->next_action = array();
        $this->next_idle = array();
        $this->action_queues = array();
        $this->idle_queues = array();
        $this->end_handles= array();
        
        $this->is_running = false;
        Logger::debug("[MQ Pump] end");
    }
    /**
     * 
     * @param unknown $queue_id
     * @param unknown $action
     */
    private function callAction($queue_id, $action){
        if ($action[3] !== null) {
            Logger::debug("[MQ $queue_id]".$action[3]);
        }
        if ($action[2] !== null) {
            try {
                call_user_func_array($action[0],$action[1]);
            } catch (\Exception $e) {
                Logger::warning("[MQ $queue_id] exception: $e");
                $action[2]($e);
            }
        } else {
            call_user_func_array($action[0],$action[1]);
        }
    }
    private $is_running = false;
    private $start_queue_id = 0;
    
    private $next_action = array(); // 保存下一个操作所在的队列
    private $next_idle = array(); // 保存空闲时下一次执行操作所在的队列
    
    private $action_queues = array(); // 执行队列
    private $idle_queues = array(); // 空闲队列
    
    private $end_handles = array();
}

?>