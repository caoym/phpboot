<?php

/**
 * $Id: Response.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * 
 * @author caoyangmin(caoyangmin@baidu.com)
 *         @brief Request
 */
namespace caoym\phprs;

use caoym\util\Verify;

/**
 * http响应包装
 * 保存http响应, 并通过sender写出
 * 并可以数组的方式设置数据
 * 如
 * $res['status'][]='200 OK'
 * $res['body'][]='...'
 * 
 * @author caoym
 */
class Response implements \ArrayAccess
{
    /**
     * 创建响应
     * @param array $sender 数据发送方法
     */
    function __construct($sender = null)
    {
        if ($sender !== null) {
            $this->sender = $sender;
        }
        if ($this->sender === null) {
            // TODO 严格检查方法的参数数量
            // 输出时按照下面数组中的顺序输出
            $this->sender = array(
                'header' => function ($_=null)
                {
                    call_user_func_array('header', func_get_args());
                },
                'status' => function ($var, $replace = true)
                {
                    header($_SERVER["SERVER_PROTOCOL"] . ' '.$var, $replace);
                },
                'cookie' =>function ($name, $value, $expire=null, $path='/', $domain=null, $secure=null){
                    if(is_string($expire)){
                        $expire = strtotime($expire);
                    }
                    setcookie($name, $value, $expire, $path, $domain, $secure);
                },
                'body' => function ($var)
                {
                    if (is_array($var)) {
                        header("Content-Type: application/json; charset=UTF-8");
                        echo json_encode($var); // TODO 自定义适配方法
                    } else {
                        echo $var;
                    }
                },
                'break'=>function($_=null){
                    //do nothing
                },
                'res'=>function ($status, $body){
                    header($_SERVER["SERVER_PROTOCOL"] . ' '.$status, true);
                    if (is_array($body)) {
                        header("Content-Type: application/json; charset=UTF-8");
                        echo json_encode($body); // TODO 自定义适配方法
                    } else {
                        echo $body;
                    }
                }
            );
        }
    }

    /**
     * (non-PHPdoc)
     * 
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->sender[$offset]);
    }

    /**
     * 通过[]操作符设置输出数据
     */
    public function &offsetGet($offset)
    {
        Verify::isTrue($this->offsetExists($offset), 'unsupported response ' . $offset);
        if (! isset($this->buffer[$offset])) {
            $this->buffer[$offset] = array();
        }
        return $this->buffer[$offset];
    }

    /**
     * (non-PHPdoc)
     * 
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        Verify::isTrue(false, 'NOT IMPL');
    }

    /**
     * (non-PHPdoc)
     * 
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        Verify::isTrue(false, 'NOT IMPL');
    }
    /**
     * 清除缓存
     * @return void
     */
    public function clear(){
        $this->buffer = array();
    }
    /**
     * 想缓存写出
     * @param $limit 取指定的项目
     * @param $func 取出后调用的方法
     * @return array:
     */
    public function flush($limit=null, $func=null)
    {
        foreach ($this->sender as $name=>$sender){
            if (!isset($this->buffer[$name])){
                continue;
            }
            if($limit !==null ){
                if($limit !== $name){
                    continue;
                }
                if($func!==null){
                    $sender = $func;
                }
            }
            $funcs = $this->buffer[$name];
            foreach ($funcs as $args) {
                // 确保所有参数均已设置
                ksort($args, SORT_NUMERIC);
                $i = 0;
                foreach ($args as $k => $v) {
                    Verify::isTrue($k === $i ++, "the no.$i arg from $name not exist");
                }
                call_user_func_array($sender, $args);
            }
            if($limit !==null){
                break;
            }
        }
    }
   /**
    * 附加更多数据
    * @param array $buffer
    */
    public function append($buffer){
        foreach ($buffer as $name => $funcs) {
            foreach ($funcs as $func){
                $this->buffer[$name][]=$func;
            }
        }
    }
    /**
     * 
     * @return multitype:
     */
    public function getBuffer(){
        return $this->buffer;
    }
    /**
     * @param unknown $buffer
     */
    public function setBuffer($buffer){
        $this->buffer = $buffer;
    }
    private $buffer = array();

    private $sender;
}

