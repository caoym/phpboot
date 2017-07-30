<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/2/21
 * Time: 下午11:46
 */

namespace PhpBoot\Workflow\Process;


use PhpBoot\Workflow\Process\Nodes\ProcessTaskContainer;
use PhpBoot\Workflow\Utils\SerializableFunc;
use PhpBoot\Workflow\Utils\Verify;

class ProcessEngine
{
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function startNewProcess(){
        $this->end = false;
        $this->queue = [];
        $this->timers = [];
        $this->listeners = [];
        $this->tokens = [];

        $this->triggerBeginEvent();
        while (count($this->queue)) {
            list($nodeName, $invokeMethod, $context) = $this->queue[0];
            $node = $this->process->getNode($nodeName);
            print "$nodeName::$invokeMethod=>";
            $node->{$invokeMethod}($context, $this);
            array_shift($this->queue);
        }
        if(!empty($this->queue) || !empty($this->timers) || !empty($this->listeners)){
            return;
        }
        $this->end or \PhpBoot\abort("process abnormal end, that means no end event triggered"); //过程没有触发end事件就结束了
    }
    private function triggerBeginEvent(){
        $this->pushTask('begin', 'handle', null);
    }
    public function triggerEndEvent(){
        $this->end = true;
    }

    public function listen()
    {

    }

    public function catchEvent($event,
                               $nodeName,
                               $invokeMethod,
                               ProcessContext $context = null)
    {

    }

    public function delayTask($event,
                              $nodeName,
                              $invokeMethod,
                              ProcessContext $context = null)
    {

    }

    /**
     * @return void
     */
    public function pushTask($nodeName, $invokeMethod, ProcessContext $context = null)
    {
        is_string($nodeName) or \PhpBoot\abort(new \InvalidArgumentException("\$nodeName must be a string"));
        is_string($invokeMethod) or \PhpBoot\abort(new \InvalidArgumentException("\$invokeMethod must be a string"));
        if (!$context) {
            $context = $this->createContext();
        }
        $this->queue[] = [$nodeName, $invokeMethod, $context];
    }

    /**
     * @param ProcessContext|null $parent
     * @return ProcessContext
     */
    public function createContext(ProcessContext $parent = null)
    {
        $context = new ProcessContext($parent);
        if(!$context->getToken()){
            $context->setToken($this->createToken());
        }
        return $context;
    }
    /**
     * @param ProcessToken|null $parent
     * @return ProcessToken
     */
    public function createToken(ProcessToken $parent = null)
    {
        $token = new ProcessToken($parent);
        if($parent){
            $parent->addChild($token);
        }
        return $token;
    }

    /**
     * @param ProcessContext[] $contexts
     * return ProcessContext;
     */
    public function mergeContexts(array $contexts){
        $new = $this->createContext();
        foreach ($contexts as $context){
            foreach ($context as $k=>$v){
                if(isset($new[$k]) && $new[$k] !== $v){
                    // TODO 警告冲突
                }
                $new[$k] = $v;
            }
        }
        return $new;
    }

    public function pushNodeStack($name, ProcessContext $context){

    }
    /**
     * @param $name
     * @return ProcessContext
     */
    public function popNodeStack($name){

    }

    /**
     * @param $name
     * @return ProcessContext[]
     */
    public function getNodeStack($name){

    }

    /**
     * @param $name
     * @return ProcessContext[]
     */
    public function popNodeStackAll($name){

    }
    /**
     * 执行队列
     * [$event,$nodeName,$invokeMethod,ProcessContext][]
     * @var array
     */
    private $queue = [];

    private $timers = [];

    private $listeners = [];

    private $tokens = [];

    /**
     * 节点的临时存储, 用于保存节点的中间状态。如并行网关等待多个输入时, 到达的数据先存储在此
     * @var array  [$nodeName, ProcessContext[]]
     */
    private $stack = [];

    /**
     * @var Process
     */
    private $process;

    private $end=true;
}