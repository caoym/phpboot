<?php
/**
 * $Id: BindReturns.php 60686 2015-03-10 10:48:49Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief 
 */
namespace phprs;
use phprs\util\Verify;

/**
 * 绑定@return 变量
 * @author caoym
 *
 */
class BindReturns
{
    /**
     * 
     * @param string $class_name
     * @param string $method_name
     * @param boolean $auto_bind_return 是否默认绑定返回值
     */
    public function __construct($class_name, $method_name, $auto_bind_return=true){
        $this->class_name = $class_name;
        $this->method_name = $method_name;
        $this->auto_bind_return = $auto_bind_return;
        //默认return输出到body
        if($auto_bind_return){
            $this->params['body'][-1] = array(0=>array(false, null, -1, null));
        }
    }
    
    /**
     * 设置绑定
     * @param $params 绑定相关参数:[目标变量 , 源变量]
     * @param $method_info 方法变量信息 [变量名=>[是否引用, 是否有默认值, 默认值]]
     */
    public function set($id, $params, $method_info){
        
        Verify::isTrue(is_array($params)||is_string($params), "{$this->class_name}::{$this->method_name} invalid @return");
        if (is_string($params)){
            $to = $params;
            $from = array();
        }else{
            $to = $params[0];
            $from = array_slice($params, 1);
        }

        $to_func = &$this->params[$to][$id];
        if($this->auto_bind_return && ($to == 'body'||$to == 'res') && isset($this->params['body'][-1])){
            unset($this->params['body'][-1]);// 指定了body输出, 去掉默认的body输出
        }
        if(0 === count($from)){
            $to_func[0]=array(false, null, -1, null,);
        }
        foreach ($from as $index=>$name){ // 输入方法 $index变量序号 $name变量名
            $is_const = (substr($name, 0, 1) !='$'); 
            if ($is_const){ // 输出常量, 不需要绑定变量
                $to_func[$index] = array(true, $name, 0, null,);//[是否常量,  值 , 参数来源位置, 参数信息]
                continue;
            }
            $name = substr($name, 1);
            //变量输出, 需要绑定
            $pos = -1;
            $step = 0;

            //是(输出)方法的第几个参数
            foreach ($method_info as $item){
                list($param_name, ) = $item;
                if($name === $param_name){
                    $pos = $step;
                    break;
                }
                $step++;
            }
            Verify::isTrue($pos !== -1, "{$this->class_name}::{$this->method_name} param: $name not found");
            //只能是引用
            list(, $is_ref, ) = $item;
            Verify::isTrue($is_ref, "{$this->class_name}::{$this->method_name} param: $name @return must be a reference");
            $to_func[$index] = array(false, $name, $pos, $item,);//[是否常量,  值 , 参数位置, 参数信息]
        }
    }
    /**
     * 绑定到函数调用的参数上去
     * @param $req
     * @param $res
     * @param array $args
     */
    public function bind($req, &$res, &$args)
    {
        //params保存的数据
        // [
        //   'body'=>[// 输出方法
        //                      [arg1, arg2, arg3], //调用列表
        //                      [arg1, arg2, arg3]
        //                 ],
        //    ]
        // ]
        //没有指定body输出, 默认使用返回值作为输出
       
        foreach ($this->params as $fun_name => $calls) {//
            foreach ($calls as $id => $call) {
                foreach ($call as $num => $arg) { // 方法
                    list ($is_const, $value, $pos, $info) = $arg;
                    if ($is_const) { // 常量,直接输出
                        $res[$fun_name][$id][$num] = $value;
                    } else if($pos === -1){ // 返回值作为变量输出
                        $res[$fun_name][$id][$num] = &$this->return_val;
                    }else{
                        if (! array_key_exists($pos, $args)) { // 没有输入,只有输出
                            list (, $is_ref, $is_optional, $default) = $info;
                            if ($is_optional) {
                                $args[$pos] = $default;
                            } else {
                                $args[$pos] = null;
                            }
                        }
                        $res[$fun_name][$id][$num] = &$args[$pos];
                    }
                }
            }
        }
       
    }
    /**
     * 设置返回值
     * @param unknown $var
     */
    public  function setReturn($var){
        $this->return_val = $var;
    }
    
    /**
     * 获取被绑定的参数列表
     * 返回的是参数位置
     * @return array
     */
    public function  getBindParamPos(){
        $params=array();
        foreach ($this->params as $fun_name => $calls) {
            foreach ($calls as $id => $call) {
                foreach ($call as $num => $arg) {
                    list ($is_const, $value, $pos, $info) = $arg;
                    if($pos !== -1){
                        $params[] = $pos;
                    }
                }
            }
        }
        return $params;
    
    }
    /**
     * @return boolean
     */
    public function isEmpty(){
        return count($this->params) ===0;
    }
    /**
     * 返回绑定的参数信息
     * @return array
     *  [[输出方法=>[[是否常量,  值 , 输出参数位置, 参数信息],..]]
     * 
     */
    public function getParams(){
        return $this->params;
    }
    private $params= array();// [目标=>[[是否常量,  值 , 参数位置, 参数信息],..]]
    private $class_name;
    private $method_name;
    private $return_val;
    private $auto_bind_return;
}

?>