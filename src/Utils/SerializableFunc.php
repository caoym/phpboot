<?php

namespace PhpBoot\Utils;

/**
 * 支持序列化的函数
 * @author caoym
 *
 */
class SerializableFunc{
    /**
     * 方法,绑定参数
     * 如
     * func,arg1,arg2
     * array('a','method1'), arg1,arg2
     */
    public  function __construct(callable $func){
        $args = func_get_args();
        $this->func = $func;
        $this->bind = array_slice($args,1);
    }
    
    /**
     * 
     * 调用时,将bind参数加在方法的最前面
     * @return mixed
     */
    public function __invoke(){
        $args = func_get_args();
        $params = $this->bind;
        foreach ($args as $arg){
            array_push($params, $arg);
        }
        $res = call_user_func_array($this->func, $params);
        foreach ($this->next as $next){
            call_user_func_array($next,$args);
        }
        return $res;
    }
    /**
     * 串行调用
     * @var callable[]
     */
    public $next=array();
    private $bind;
    private $func;
}

?>