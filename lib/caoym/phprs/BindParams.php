<?php
/**
 * $Id: BindParams.php 60686 2015-03-10 10:48:49Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief 
 */
namespace caoym\phprs;

use caoym\util\Verify;
use caoym\util\exceptions\BadRequest;

//TODO: 把绑定参数的各种信息保存在数组中, 已经有点难看了.
/**
 * 
 * @author caoym
 * 绑定响应
 */
class BindParams{
    /**
     * @param string $class_name
     * @param string $method_name
     */
    public function __construct($class_name, $method_name){
        $this->class_name = $class_name;
        $this->method_name = $method_name;
    }
    /**
     * 设置绑定
     * @param int $id 参数声明的序号
     * @param $params 绑定相关参数:[目标变量 , 源变量]
     * @param $method_info 方法变量信息 [ [变量名, 是否引用, 是否有默认值, 默认值], ... 
     */
    public function set($id, $params, $method_info){
        Verify::isTrue(is_array($params) && count($params) ==2, "{$this->class_name}::{$this->method_name} invalid @param");
        list($to, $from) = $params;
        $pos = -1;
        $step = 0;
        foreach ($method_info as $item){
            list($param_name, ) = $item;
            if($to === $param_name){
                $pos = $step;
                break;
            }
            $step++;
        }
        Verify::isTrue($pos !== -1, "{$this->class_name}::{$this->method_name} param: $to not found");
        Verify::isTrue(!isset($this->params[$pos]),"{$this->class_name}::{$this->method_name} param: $to repeated bound" );
        $this->params[$pos] = array(substr($from, 0, 1) !='$', $from, $item,$id,);//[是否常量,  值 , 参数信息]
    }
    /**
     * 绑定到函数调用的参数上去
     * @param $req
     * @param $res
     * @param array $args
     */
    public function bind($req, &$res, &$args){
        foreach ($this->params as $pos=>$param){
            list($is_const, $value, $info) = $param;
            if($is_const){// 常量
                $args[$pos] = $value;
            }else{ //变量
                list(, $is_ref, $is_optional, $default) = $info;
                $found = $req->find($value, $is_ref, $default);
                if(!$found[1]){
                    Verify::isTrue($is_optional, new BadRequest("{$this->class_name}::{$this->method_name} $value not found in request"));
                    $args[$pos] = $default;
                }else{
                    if($is_ref){
                        $args[$pos] = &$found[0];
                    }else{
                        $args[$pos] = $found[0];
                    }
                    
                }
            }
        }
    }
    /**
     * 获取被绑定的参数列表
     * 返回的是参数位置
     * @return array
     */
    public function  getBindParamPos(){
        $params=array();
        foreach ($this->params as $pos=>$param){
            $params[] = $pos;
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
     * 获取绑定参数信息
     * @return array
     * [ [输入参数位置=>[是否常量, 值, 参数信息]], ...]
     * 其中参数信息形式
     * [变量名, 是否引用, 是否有默认值, 默认值]
     */
    public function getParams(){
        return $this->params;
    }
    private $params= array();
    private $class_name;
    private $method_name;
}
