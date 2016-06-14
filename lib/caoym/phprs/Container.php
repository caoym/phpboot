<?php
/**
 * $Id: Container.php 58155 2015-01-05 14:45:30Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief RestfulApiContainer
 */
namespace caoym\phprs;
use caoym\util\AnnotationReader;
use caoym\util\Verify;
use caoym\util\Logger;

/**
 * restful api 容器
 * @author caoym
 * 
 */
class Container{
    /**
     * @param string $class 类名
     * @param string $method 方法名, 如果为空, 则加载此类的所有方法
     */
    function __construct($class, $method = null){
        $this->load($class, $method);
    }
	/**
	 * 
	 * @param string $class 类名
	 * @param string $method ==null时load所有方法, !==null时load指定方法
	 */
	public function load($class, $method){
	    $this->class = $class;
	    //获取方法
	    $reflection = new \ReflectionClass($class);
	    $reader= new AnnotationReader($reflection);
	    $class_ann = $reader->getClassAnnotations($reflection);
	    Verify::isTrue(isset($class_ann['path']), $class.' @path not found');
	    Verify::isTrue(count($class_ann['path'])===1, $class.' @path ambiguity');
	    $path = $class_ann['path'][0]['value'];
	    $this->path = $path;
        $specified = $method;
	    foreach ($reflection->getMethods() as $method){
	        if($specified !== null && $specified !==  $method->getName()){
	            Logger::DEBUG("specified method: $specified, ignore $class::{$method->getName()}");
	            continue;
	        }        
	        $anns = $reader->getMethodAnnotations($method, false);
	        if(!isset($anns['route'])){
	            Logger::DEBUG("no @route, ignore $class::{$method->getName()}");
	            continue;
	        }
	        //Verify::isTrue(count($anns['route']) == 1, "$class::{$method->getName()} @route repeated set");
	        $invoker =  $this->factory->create('caoym\phprs\Invoker', array($this, $method) );
	        foreach ($anns['route'] as $ann){
	            $route = $ann['value'];
	            Verify::isTrue(is_array($route) && count($route)==2,
	            "$class::{$method->getName()} syntax error @route, example: @route({\"GET\" ,\"/api?a=2\"})"
	            );
	            list($http_method, $uri) = $route;
	            $this->routes[$http_method][] = array($path.'/'.$uri, $invoker);
	        }
	       
	        foreach ($anns as $type =>$v){
	            if($type == 'route'){
	                continue;
	            }
	            $id = 0;
	            foreach ($v as $ann){
	                if(!is_array($ann) || !isset($ann['value'])) {
	                    continue;
	                }
	                $invoker->bind($id++, $type, $ann['value']);
	                continue;
	            }
	        }
	        
	        //检查是否所有必须的参数均已绑定
	        $invoker->check();
	    }
	    //属性注入
	    /*foreach ($reflection->getProperties() as $property ){
	        foreach ( $reader->getPropertyAnnotations($property) as $id => $ann){
	            if($id !== 'inject') { continue;}
	            $name = $property->getName();
	            if($name == "ioc_factory"){// ioc_factory由工厂负责注入
	                //TODO: 用@ioc_factory替代ioc_factory
	                continue;
	            }
	            Verify::isTrue(count($ann) ===1, "$class::$name ambiguity @inject");
	            Verify::isTrue(isset($ann[0]['value']), "$class::$name invalid @inject");
	            Verify::isTrue(is_string($ann[0]['value']), "$class::$name invalid @inject");
	            $this->injectors[] = new Injector($this, $name, $ann[0]['value']);
	        }
	    }*/
	   
	    
	}
	/**
	 * 获取API实现类的实例
	 * @param Request $request
	 * @return object
	 */
    public function getImpl($request){
        Verify::isTrue($request !== null);
        if($this->impl === null){
            $injected =  &$this->injected;
            $injected = array();
            $this->impl = $this->factory->create($this->class, null, null, function($src, &$succeeded)use($request, &$injected){
                list($val, $found) = $request->find($src);
                $succeeded  = $found;
                $injected[$src]=$val;
                return $val;
            });
            asort($injected);
        }
        return $this->impl;
    }
    /**
     * 获取实例被注入的方法
     * 只有实例被创建后才能取到值
     * @return array
     */
    public function getInjected(){
        return $this->injected;
    }
    ///**
    // * 从http请求中提取属性
    // * @param RestfulApiRequest $request http请求
    // */
    //public function inject($request){
    //    foreach ($this->injectors as $inject){
    //        $inject($request);
    //    }
    //}
    ///**
    // * 获取注入的依赖
    // * @return arra:
    // */
    //public function getInjectors(){
    //    return $this->injectors;
    //}
    //每次处理请求时属性被重新注入到API实例
	//private $injectors=array();
	public $routes=array(); //['GET'=>[Invoker,Invoker,Invoker...],'POST',....];
	//API 实现类;
    public $class;
    //API 实例;
    private $impl;
    private $injected;// 被注入的属性, 记录下来, 可以作为缓存key的一部分
    /** @inject("ioc_factory") */
    public $factory;
    
    public $path;
}
