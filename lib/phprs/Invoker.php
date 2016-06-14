<?php
/**
 * $Id: Invoker.php 63816 2015-05-15 11:35:31Z caoyangmin $
 * 
 * @author caoyangmin(caoyangmin@baidu.com)
 *         @brief
 */
namespace phprs;

use phprs\util\Verify;
use phprs\util\Logger;
use phprs\util\exceptions\BadRequest;
use phprs\util\CheckableCache;

/**
 * api调用包装
 * 从请求中提取api所需的参数, 调用API, 并将结果输出到Response对象
 * 
 * @author caoym
 *        
 */
class Invoker
{
    /**
     * @param Container $ins
     *            被调用的实例容器
     * @param string $method
     *            被调用的实例方法
     */
    public function __construct($ins, $method)
    {
        $this->ins = $ins;
        if($this->cache === null){
            $this->checkAbleCache = $this->factory->create('phprs\util\Cache');
        }else{
            $this->checkAbleCache = new CheckableCache($this->cache);
        }
         
        $this->method_name = $method->getName();
        foreach ($method->getParameters() as $param) {
            $this->method_args[] = array(
                $param->getName(), // name
                $param->isPassedByReference(), // ref
                $param->isOptional(), // isOptional
                $param->isOptional() ? $param->getDefaultValue() : null,
            ) // default
            ;
        }
        $this->bind = array(
            'param' => new BindParams($this->ins->class, $this->method_name),
            'return' => new BindReturns($this->ins->class, $this->method_name),
            'throws' => new BindThrows($this->ins->class, $this->method_name),
            'cache' => new BindReturns($this->ins->class, $this->method_name, false),
        );
    }

    /**
     * 绑定参数,返回值,异常
     * @param int $id 
     *  参数id      
     * @param string $type 绑定类型 param/return/throws
     * @param $param
     *            param:
     *            [['arg_name'=>'$._SERVER.REQUEST_URI'],...]
     *            [[参数名=>jsonpath表达式],...]
     *            指定api接口参数从http请求中获取的方式
     *            returns:
     *            指定哪些参数被作为返回值
     *            [
     *            ['status','200 OK'], 200 OK 作为常量, 输出到status
     *            ['cookie','$arg0'], $arg0变量输出到cookie
     *            ['body'], 未指定变量, 则取返回值输出
     *            ]
     *            throws 指定根据不同的异常设置响应信息
     *            [
     *            ['MyException','status' , '404 Not Found'],
     *            ['MyException','body','$msg'],
     *            ['Exception','status','500 Internal Server Error'],
     *            ]
     */
    public function bind($id, $type, $param)
    {
        if (! isset($this->bind[$type])) {
            return;
        }
        $this->bind[$type]->set($id, $param, $this->method_args);
    }

    /**
     * 执行API
     * 
     * @param Request $request 请求
     * @param Response $response 响应
     */
    public function __invoke($request, &$response)
    {
        $args = array();
        //绑定参数和返回值
        $this->bind['param']->bind($request, $response, $args);
        $this->bind['return']->bind($request, $response, $args);
        //利用参数绑定的能力，提取@cache注释的信息
        $cache_ttl = 0;
        $cache_check = null;
        $cache_res = new Response(array(
            'ttl' => function ($param) use(&$cache_ttl)
            {
                $cache_ttl = $param;
            },
            'check' => function ($param) use(&$cache_check)
            {
                $cache_check = $param;
            },
            'body'=>function ($_=null){}
        ));
        $this->bind['cache']->bind($request, $cache_res, $args);
        
        $use_cache = !$this->bind['cache']->isEmpty();
        
        $given = count($args);
        if ($given === 0) {
            $required_num = 0;
        } else {
            ksort($args, SORT_NUMERIC);
            end($args);
            $required_num = key($args) + 1;
        }
        
        Verify::isTrue($given === $required_num, new BadRequest("{$this->ins->class}::{$this->method_name} $required_num params required, $given given")); // 变量没给全
        $cache_key = null;
        if ($use_cache) {
            //输入参数包括函数参数和类注入数据两部分
            //所以以这些参数的摘要作为缓存的key
            $injected = $this->ins->getInjected();
            $cache_res->flush();//取出cache参数
            $cache_key = "invoke_{$this->ins->class}_{$this->method_name}_" . sha1(serialize($args).serialize($injected).$cache_ttl);
            $succeeded = false;
            $data = $this->checkAbleCache->get($cache_key, $succeeded);
            if ($succeeded && is_array($data)) {
                $response->setBuffer($data);
                $response->flush();
                Logger::info("{$this->ins->class}::{$this->method_name} get response from cache $cache_key");
                return;
            }
        }
        $impl = $this->ins->getImpl($request);
        //
        if (!$this->bind['throws']->isEmpty()) {
            try {
                $res = call_user_func_array(array(
                    $impl,
                    $this->method_name,
                ), $args);
            } catch (\Exception $e) {
                $response->clear(); // 清除之前绑定的变量, 异常发生时可能已经写入了一些参数
                $this->bind['throws']->bind($request, $response, $e);
                $response['break'][][0]=true;
                $response->flush();
                return;
            }
        } else {
            $res = call_user_func_array(array(
                $impl,
                $this->method_name,
            ), $args);
        }
        $this->bind['return']->setReturn($res);
        if ($use_cache) {
            $this->checkAbleCache->set($cache_key, $response->getBuffer(), $cache_ttl, $cache_check);
            Logger::info("{$this->ins->class}::{$this->method_name} set response to cache $cache_key, ttl=$cache_ttl, check=".($cache_check===null?'null':get_class($cache_check)));
        }
        $response->flush();
    }

    /**
     * 获取被绑定的参数列表
     * 返回的是参数位置
     * 
     * @return array
     */
    public function getBindParamPos()
    {
        return array_merge($this->bind['return']->getBindParamPos(), $this->bind['param']->getBindParamPos(), $this->bind['cache']->getBindParamPos());
    }

    /**
     * 检查参数是否均已经绑定
     */
    public function check()
    {
        $params = $this->getBindParamPos();
        foreach ($this->method_args as $id => $arg) {
            list ($name, $is_ref, $is_optional, $default) = $arg;
            if (false === array_search($id, $params)) {
                Verify::isTrue($is_optional, "{$this->ins->class}::{$this->method_name} param: $name not be bound");
            }
        }
    }
    /**
     * @return string
     */
    public function getMethodName(){
        return $this->method_name;
    }
    /**
     * 绑定的参数
     */
    public function getParams(){
        return $this->bind['param'];
    }
    /**
     * 绑定的返回值
     */
    public function getReturns(){
        return $this->bind['return'];
    }
    /**
     * 绑定的异常
     */
    public function getThrows(){
        return $this->bind['throws'];
    }
    
    /**
     * @return string
     */
    public function getClassName(){
        return $this->ins->class;
    }
    /**
     * @return object
     */
    public function getContainer(){
        return $this->ins;
    }
    public  $method_name;

    private $method_args = array();

    private $ins;
    // 绑定的变量
    private $bind = array();

    /** 
     * @property 
     * @var phprs\util\KVCatchInterface
     */
    private $cache=null;
    private $checkAbleCache;
    /** @inject("ioc_factory") */
    private $factory;
}