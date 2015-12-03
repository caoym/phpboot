<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: BindThrows.php 57067 2014-12-15 05:39:13Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief 
 */
namespace caoym\phprs;
use caoym\util\Verify;
/**
 * 
 * @author caoym
 *
 */
class BindThrows
{
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
     * @param $params 绑定相关参数:[目标变量 , 源变量]
     * @param $method_info 方法变量信息 [变量名=>[是否引用, 是否有默认值, 默认值]]
     */
    public function set($id, $params, $method_info){
    
        Verify::isTrue(is_array($params) && count($params) >=2, "{$this->class_name}::{$this->method_name} invalid @throws");
        $exception = $params[0];
        $to = $params[1];
        $from = array_slice($params, 2);
        $to_func = &$this->params[$exception][$to][$id];
        foreach ($from as $index=>$name){ // 输入方法 $index变量序号 $name变量名
            if(is_array($name)){
                $is_const = true;
            }else{
                $is_const = (substr($name, 0, 1) !='$');
            }
            Verify::isTrue($is_const, "{$this->class_name}::{$this->method_name} dynamic variable not supported by @throws");
            $to_func[$index] = $name;
        }
    }
    /**
     * 绑定到函数调用的参数上去
     * @param $req
     * @param $res
     * @param $e 异常值
     */
    public function bind($req, &$res, $e)
    {
        if(!isset($this->params['body'])){
            $res['body'][0][0] = $e;
        }
        //先处理完全匹配的
        $matched = false;
        $funcs = array(); //输出方法
        foreach ($this->params as $exce_name => $calls){
            if(get_class($e) == $exce_name){
                $funcs[] = $calls;
            }
        }
        //没有完全匹配的异常, 尝试匹配父类
        if(count($funcs)===0){
            foreach ($this->params as $exce_name => $calls){
                if(is_a($e, $exce_name) ){
                    $funcs[] = $calls;
                }
            }
        }
        // [
        // 'body'=>[
        // [0=>[arg1, arg2, arg3]],
        // ]
        // ]
        foreach ($funcs as $id=>$calls) {
            foreach ($calls as $fun_name => $call) {
                foreach ($call as  $arg) {
                    $res[$fun_name][$id] = $arg;
                }
            }
        }
        if(count($funcs) ===0 ){
            throw  $e;
        }

    }
    /**
     * @return boolean
     */
    public function isEmpty(){
        return count($this->params) ===0;
    }
    /**
     * 返回绑定的变量信息
     * @return array
     * [异常名=>[输出方法]=>[输出参数位置=>[值 , 参数信息],..]]
     */
    public function getParams(){
        return $this->params;
    }
    private $params= array();// [异常=>[目标]=>[位置=>[值 , 参数信息],..]]
    private $class_name;
    private $method_name;
}