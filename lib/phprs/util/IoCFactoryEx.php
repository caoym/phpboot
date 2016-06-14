<?php
namespace phprs\util;

/**
 * 加强版IoCFactory...
 * 1. 创建的是指定类的容器实例，而不是类的实例
 * 2. 在第一次调用时才创建类的实例
 * 3. 支持@cache注释，对类的接口进行缓存
 * @author caoym
 *
 */
class IoCFactoryEx extends IoCFactory
{
    public function __construct($conf=null, $dict=null, $metas=null){
        parent::__construct($conf, $dict, $metas);
    }
    /**
     * 根据id创建对象(的容器)实例
     * @param string $id
     * @param array $properties 类属性, 覆盖配置文件中的属性
     * @param callable $injector fun($src), 获取注入值的方法
     * @param callable $init fun($inst, &$got) 初始化实例, 在创建后, 调用构造函数前
     * @param array $construct_args 构造函数的参数
     * @return object
     */
    public function create($id, $construct_args=null, $properties=null, $injector=null, $init=null ){
        $meta = $this->getMetaInfo($this->getClassName($id));
        if(isset($meta['cache'])){
            return new IoCObjectWrap($this, $id, $construct_args, $properties, $injector, $init); 
        }
        return $this->createRawObject($id, $construct_args, $properties, $injector, $init);
        
    }
    /**
     * 根据id创建对象(的容器)实例, 不使用容器
     * @param string $id
     * @param array $properties 类属性, 覆盖配置文件中的属性
     * @param callable $injector fun($src), 获取注入值的方法
     * @param callable $init fun($inst, &$got) 初始化实例, 在创建后, 调用构造函数前
     * @param array $construct_args 构造函数的参数
     * @return object
     */
    public function createRawObject($id, $construct_args=null, $properties=null, $injector=null, $init=null ){
        return parent::create($id, $construct_args, $properties, $injector, $init);
    }
}
/**
 * 容器
 * @author caoym
 *
 */
class IoCObjectWrap{
    public function __construct($factory, $id, $construct_args, $properties, $injector, $init){
        $this->__impl__ = $factory->createRawObject('phprs\\util\\IoCContainer', [
            'id' => $id,
            'construct_args' => $construct_args,
            'properties' => $properties,
            'injector' => $injector,
            'init' => $init
        ]);
    }
    public function __get($name){
        return $this->__impl__->getObj()->$name;
    }
    public function __set($name , $value ){
        $this->__impl__->getObj()->$name = $value;
    }
    public function __isset ($name){
        return  isset($this->__impl__->getObj()->$name);
    }
    public function __unset ($name){
        unset($this->__impl__->getObj()->$name);
    }
    
    public function __call($name, $arguments) {
        $arguments[0]=111;
        return;
        return $this->__impl__->callThroughCache($name, $arguments);
    }

    /**
     * @var IoCContainerImpl
     */
    private $__impl__;
}
/**
 * 
 * @author caoym
 */
class IoCContainer{
    
    public function __construct($id, $construct_args, $properties, $injector, $init){
        $this->id = $id;
        $this->construct_args = $construct_args;
        $this->properties = $properties;
        $this->injector = $injector;
        $this->init = $init;
    }
    public function getObj(){
        if($this->obj==null){
            $this->obj = $this->factory->createRawObject(
                $this->id ,
                $this->construct_args,
                $this->properties,
                $this->injector,
                $this->init
            );
        }
        return $this->obj;
    }
    /**
     * 
     */
    public function callThroughCache($method, $arguments){
        $op = $this->getCacheOptions($method);
        if($op){
            $got = false;
            $key = $this->genKey($method, $arguments);
            $res = $this->cache->get($key, $got);
            if($got){
                return $res;
            }else{
                /*static $methods = [];
                $name = $this->factory->getClassName($this->id).'::'.$method;
                if(!array_key_exists($name, $methods)){
                    $refl = new \ReflectionClass($this->factory->getClassName($this->id));
                    $methods[$name] = $refl->getMethod($method);
                }
                $res = $methods[$name]->invokeArgs($this->getObj(), $arguments);
                */
                $res = call_user_func_array([$this->getObj(),$method], $arguments);
                $this->cache->set($key, $res, $op['ttl'], isset($op['checker'])?$op['checker']:null);
                return $res;
            }
        }else{
            return call_user_func_array([$this->getObj(), $method], $arguments);
        }
    }
    
    public function getCacheOptions($method){
        if(!array_key_exists($method, $this->cachedMethods)){
            $meta = $this->factory->getMetaInfo($this->factory->getClassName($this->id));
            if(isset($meta['cache'][$method]['value'])){
                $val = $meta['cache'][$method]['value'];
                list($k, $v) = $val;
                Verify::isTrue($k == 'ttl', "no TTL with @cache in $method");
                $this->cachedMethods[$method][$k] = $v;
            }
        }
        return $this->cachedMethods[$method];
    }
    private function genKey($method, $arguments){
        return '_ioc_'.$this->id.$method.sha1(serialize($arguments));
    }
    private $obj;
    private $id;
    private $construct_args;
    private $properties;
    private $injector;
    private $init;
    private $cachedMethods=[];
    /**
     * @inject("factory")
     * @var IoCFactoryEx
     */
    private $factory;
    /**
     * @property
     * @var CheckableCache
     */
    private $cache;
}
