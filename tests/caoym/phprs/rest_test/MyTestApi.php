<?php
use caoym\util\Verify;

class MyException extends \Exception{
   
}
/**
 * 
 * @author caoym
 * @path("/testapi")
 */
class MyTestApi{
    /**
     * @route({"GET", "/func?b=2"})
     * @param({"arg0", "$.arg0"})
     * @param $arg0 
     * @param({"arg1" , "$.arg1"}) 
     * @param({"arg3" , "$.arg3"}) 
     * @return({"status", "$arg1"}) 
     * @return({"header", "$arg1"}) 
     * @return({"header", "$arg2"}) 
     * @return({"header", "const header"})  常量
     * @return({"cookie", "token","$arg1","3000","$arg2"}) 多个变量
     * @return("body")
     * @throws({"Exception", "status", "404 Bad Request"})
     * @throws({"Exception", "body", "404 Bad Request"})
     */
    public function func($arg0, &$arg1, &$arg2, $arg3='arg3 default'){
        //$arg1 不设置
        $arg2='return $arg2';
        return ['arg0'=>$arg0, 'arg1'=>$arg1,'arg2'=>$arg2,'arg3'=>$arg3]; 
    }
    /**
     * @route({"GET", "/func1"})
     * 无效的参数绑定: 源不存在
     * @param({"arg0", "$.unknown"})
     */
    public function funcInvalidBind1($arg0){
       return 'funcInvalidBind called';
    }
    /**
     * @route({"GET", "/func2"})
     * 无效的参数绑定: 绑定的变量不存在
     * @param({"unknown", "$._SERVER"})
     * @param({"arg", "$._SERVER"})
     */
    public function funcInvalidBind2($arg){
        return 'funcInvalidBind called';
    }
    /**
     * @route({"GET", "/func7"})
     * 无效的参数绑定: 有变量没被绑定
     * @param({"arg0", "$._SERVER"})
     */
    public function funcInvalidBind7($arg0, $arg1){
        return 'funcInvalidBind called';
    }
    
    /**
     * @route({"GET", "/func3"})
     * 无效的参数绑定: 返回值不是引用
     * @return({"body", "$arg0"})
     */
    public function funcInvalidBind3($arg0){
        return 'funcInvalidBind called';
    }
    
    /**
     * @route({"GET", "/func4"})
     * 无效的参数绑定: 返回值不存在
     * @return({"body", "$arg1"})
     */
    public function funcInvalidBind4($arg0){
        return 'funcInvalidBind called';
    }
    
    /**
     * @route({"GET", "/func5"})
     * 无效的参数绑定: 参数对应的绑定不存在,有默认值
     * @param({"arg1", "$.unknown"})
     */
    public function funcInvalidBind5($arg1='default'){
        return 'funcInvalidBind called';
    }
 
    /**
     * 异常完全匹配
     * @route({"GET", "/func8"})
     * @throws({"MyException", "status", "500"})
     * @throws({"MyException", "body", "MyException"})
     * @throws({"Exception", "body", "Exception"})
     */
    public function funcWithException1(){
        throw new \MyException();
    }

    /**
     * 异常父类匹配
     * @route({"GET", "/func9"})
     * @throws ({"UException", "body", "UException"})
     * @throws ({"Exception", "body", "Exception"})
     */
    public function funcWithException2()
    {
        throw new \MyException();
    }

    /**
     * 异常父类匹配
     * @route({"GET", "/func10"})
     * @param({"arg0", "$._SERVER.REQUEST_URI"})
     * @param({"arg1", "$.arg1.arg1"})
     */
    public function funReferenceReqParam(&$arg0, &$arg1='default', &$arg2 = 'default')
    {
        $arg0 = 'funReferenceReqParam arg0';
        $arg1 = 'funReferenceReqParam arg1';
        Verify::isTrue($arg2 == 'default');
    }
    /** @inject("$.header") */
    public $header;
}