<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: IoCFactory.php 64919 2015-06-08 05:48:03Z caoyangmin $
 * TODO: Reflection有性能问题
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief
 * IoCFactory
 */
namespace caoym\util;

/**
 * 依赖注入工厂
 * 创建实例, 并根据配置注入依赖
 * @author caoym
 *        
 */
class IoCFactory
{
    /**
     * @param string|array $conf 文件或者配置数组
     * 配置数组格式如下:
     * [
     *  id=>[
     *  "class"=>类名,
     *  "singleton"=>false, 是否是单例, 如果是, 则只会创建一份(同一个工厂内)
     *  "pass_by_construct"=false, 属性是否通过构造函数传递, 如果不是, 则通过访问属性的方式传递
     *  "properties"=>{
     *      属性名=>属性值
     *  }
     * @param array $dict 设置字典
     * 配置文件中, 属性值可以使用{key}的方式指定模板变量, 注入属性到实例是这些模板变
     * 量会被替换成setDict方法设置的值
     * @param array $metas 类元信息, 如果不指定, 则自动从类文件中导出
     * ]
     */
    public function __construct($conf=null, $dict=null, $metas=null)
    {
        if($conf === null){
            $this->conf = array();
        }elseif(is_array($conf)){
            $this->conf = $conf;
        }else{
            Verify::isTrue(is_file($conf), "$conf is not a valid file");
            Verify::isTrue(false !== ($data = file_get_contents($conf)), "$conf open failed");
            $data = self::clearAnnotation($data);
            Verify::isTrue($this->conf = json_decode($data,true), "$conf json_decode failed with ".json_last_error());
            $this->conf_file = $conf;
        }
        if($dict !== null){
            $this->conf = $this->replaceByDict($this->conf, $dict);
        }
        $this->metas = $metas;
    }
    /**
     * 去掉注解
     * @param unknown $text
     */
    static public function clearAnnotation($text){
        return AnnotationCleaner::clean($text);
    }
    /**
     * 获取配置的属性
     * @param string $id
     * @return array|null
     */
    public function getConf($id=null){
        if($id === null){
            return $this->conf;
        }else{
            if(isset($this->conf[$id]) && isset($this->conf[$id]['properties'])){
                return $this->conf[$id]['properties'];
            }
            return null;
        }
    }
    /**
     * 
     * @param string $id
     */
    public function getClassName($id=null){
        if(isset($this->conf[$id])){
            $class = $this->conf[$id];
            if(isset($class['class'])){
                return $class['class'];
            }else{
                return $id;
            }
        }else{
            return $id;
        }
    }
    /**
     * 根据id得到对象实例
     * //TODO 单实例间互相引用会是问题
     * @param string $id
     * @param array $properties 类属性, 覆盖配置文件中的属性  
     * @param callable $injector fun($src), 获取注入值的方法
     * @param callable $init fun($inst, &$got) 初始化实例, 在创建后, 调用构造函数前
     * @param array $construct_args 构造函数的参数
     * @return object
     */
    public function create($id, $construct_args=null, $properties=null, $injector=null, $init=null )
    {
        if($properties === null){
            $properties = array();
        }
        if($construct_args===null){
            $construct_args = array();
        }
        $ins = null;
        $is_singleton = false;
        $class_name = $this->getClassName($id);
        if(isset($this->conf[$id])){
            $class = $this->conf[$id];
            $class_refl = new \ReflectionClass($class_name);
            // 如果是单例
            // 带构造参数的类不支持单例
            if (count($properties) ===0 && count($construct_args) === 0 && isset($class['singleton']) && $class['singleton']) {
                $is_singleton = true;
                if (isset($this->singletons[$id])) {
                    $ins = $this->singletons[$id];
                    Logger::info("get {$id}[$class_name] as singleton");
                    return $ins;
                }
            }
            if(isset($class['properties'])){
                $properties = array_merge($class['properties'], $properties);
            }

            if (isset($class['pass_by_construct']) && $class['pass_by_construct']){ //属性在构造时传入
                Verify::isTrue(count($construct_args) ===0, "$class_name pass_by_construct"); //构造时传输属性, 所以不能再传入其他构造参数
                //组合构造参数
                $construct_args = $this->buildConstructArgs($class_refl, $properties);
                $properties=array();
            }
        }else{
            $class_refl = new \ReflectionClass($class_name);
        }
        if (isset($class['pass_by_construct']) && $class['pass_by_construct']){
            $ins = $class_refl->newInstanceArgs($construct_args);
            $meta = $this->getMetaInfo($class_refl);
            $this->injectDependent($class_refl, $ins, $meta, $properties, $injector);
        }else{
            $nti = new NewThenInit($class_refl);
            $ins = $nti->getObject();
            $meta = $this->getMetaInfo($class_refl);
            $this->injectDependent($class_refl, $ins, $meta, $properties, $injector);
            if($init !==null){
                $init($ins);
            }
            $nti->initArgs($construct_args);
        }
        if ($is_singleton){
            $this->singletons[$id] = $ins;
        }
        Logger::info("create {$id}[$class_name] ok");
        return $ins;
    }
    /**
     * 取配置文件路径, 如果是通过数组创建的, 则返回null
     * @return string|NULL
     */
    public function getConfFile(){
        return $this->conf_file;
    }
    /**
     * 配置中是否有指定id的类
     * @param string $id
     * @return boolean
     */
    public function hasClass($id){
        return isset($this->conf[$id]);
    }
    /**
     * 设置属性值, 允许设置private/protected属性
     * @param $refl
     * @param object $class
     *            类名或实例
     * @param string $name
     *            属性名
     * @param mixed $value
     *            属性值
     */
    static function setPropertyValue($refl, $ins, $name, $value)
    {
        Verify::isTrue($m = $refl->getProperty($name));
        $m->setAccessible(true);
        $m->setValue($ins, $value);
    }
    /**
     * 取属性值
     * @param $refl
     * @param object $ins
     * @param string $name
     * @return mixed
     */
    static function getPropertyValue($refl, $ins, $name)
    {
        Verify::isTrue($m = $refl->getProperty($name));
        $m->setAccessible(true);
        return $m->getValue($ins);
    }
    /**
     * 获取元信息
     * 会缓存
     * @param string $class
     * @return array
     */
    public function getMetaInfo($class){
        if(is_string($class)){
            $refl = new \ReflectionClass($class);
        }else{
            $refl = $class;
        }
        $name = $refl->getName();
        if($this->metas !==null && isset($this->metas[$name])){
            return $this->metas[$name];
        }
        static $cache = null;
        if($cache === null){
            $cache = new Cache();
        }
        $succeeded = false;
        $cache_key = 'meta_'.sha1($refl->getFileName().'/'.$name);
        $data = $cache->get($cache_key, $succeeded);
        if($succeeded){
            return $data;
        }
        $data = MetaInfo::get($name);
        $files = [$refl->getFileName()];
        $parent = $refl->getParentClass();
        if($parent){
            $files[] = $parent->getFileName();
        }
        foreach ($refl->getInterfaces() as $i){
            $files[] = $i->getFileName();
        }
        $cache->set($cache_key, $data, 60, new FileExpiredChecker($files));
        return $data;
    }
    
    /**
     * 根据属性组合构造参数
     * @param ReflectionClass $class
     * @param array $properties
     * @return array
     */
    private function buildConstructArgs($class, $properties){   
        if($properties===null) {
            return array();
        }
        if(count($properties)==0){
            return array();
        }
        $refMethod = $class->getConstructor();
        $params = $refMethod->getParameters();
        $args = array();
        foreach ($params as $key => $param) {
            $param_name = $param->getName();
            if(isset($properties[$param_name])){
                $args[$key] = $this->getProperty($properties[$param_name]);
            }else{
                Verify::isTrue($param->isOptional(), "{$class->getName()}::__construct miss required param: $param_name");//参数没有指定, 除非是可选参数
                try {
                    $defv = $param->getDefaultValue();
                    $args[$key] = $defv;
                } catch (\Exception $e) {
                }
                
            }
        }
        return $args;  
    }
    /**
     * 获取属性
     * 替换属性中的{}和@标记
     * @param string $value
     * @return object|string
     */
    private function getProperty($value){
        if (is_string($value) && substr($value, 0, 1) == '@') {
            return $this->create(substr($value, 1));
        } else {
            return $value;
        }
    }

    /**
     * 注入依赖
     * @param ReflectionClass $refl;
     * @param unknown $ins        
     * @param unknown $meta         
     * @param unknown $properties     
     * @return void       
     */
    public function injectDependent($refl, $ins, $meta, $properties, $injector=null)
    {
        $defaults=array();
        $class_name = $refl->getName();
        $class_defaults = $refl->getDefaultProperties();
        if(isset($meta['property']) ){
            foreach ($meta['property'] as $property => $value) {
                //参数是否可选
                if(isset($value['value']) && isset($value['value']['optional']) && $value['value']['optional']){
                    continue;
                }
                //设置了默认值
                if(isset($value['value']) && isset($value['value']['default'])){
                    $defaults[$property] = $value['value']['default'];
                    continue;
                }
                // 是否设置了默认值
                if (array_key_exists($property, $class_defaults)) {
                    continue;
                }
                Verify::isTrue(array_key_exists($property, $properties), "$class_name::$property is required");
            }
        }
        // 设置属性
        if ($properties !== null) {
            foreach ($properties as $name => $value) {
                unset($defaults[$name]);
                $v = $this->getProperty($value);
                self::setPropertyValue($refl, $ins, $name, $v);
            }
        }
        // 注入依赖
        if(isset($meta['inject'])){
            foreach ($meta['inject'] as $property => $value) {
                //先设置必须的属性
                if(is_array($value['value'])){
                    $src = $value['value']['src'];
                    //参数是否可选
                    if(isset($value['value']) && isset($value['value']['optional']) && $value['value']['optional']){
                        continue;
                    }
                    //设置了默认值
                    if(isset($value['value']) && isset($value['value']['default'])){
                        $defaults[$property] = $value['value']['default'];
                        continue;
                    }
                }else{
                    $src = $value['value'];
                }
                //是否设置了默认值
                if(array_key_exists($property, $class_defaults)){
                    continue;
                }
                if ($src === "ioc_factory" || $src == "factory"){
                    continue;
                }else{
                    $got = false;
                    Verify::isTrue($injector !==null , "$class_name::$property is required");
                    $val = $injector($src, $got);
                    Verify::isTrue($got , "$class_name::$property is required");
                    self::setPropertyValue($refl, $ins, $property, $val);
                    unset($meta['inject'][$property]);
                }
            }
            //在设置可选的
            foreach ($meta['inject'] as $property => $value) {
                if(is_array($value['value'])){
                    $src = $value['value']['src'];
                }else{
                    $src = $value['value'];
                }
                if ( $src == "ioc_factory" || $src == "factory") {
                    self::setPropertyValue($refl, $ins, $property, $this);
                    unset($defaults[$property]);
                }else if($injector){
                    $val = $injector($src, $got);
                    if($got){
                        self::setPropertyValue($refl, $ins, $property, $val);
                        unset($defaults[$property]);
                    }
                }  
            }
        }
        // 设置默认属性
        foreach ($defaults as $name => $value ){
            unset($defaults[$name]);
            $v = $this->getProperty($value);
            self::setPropertyValue($refl, $ins, $name, $v);
        }
    }

    /**
     * 替换字典
     * @see setDict
     * @param string|array $value
     * @return void
     */
    private function  replaceByDict($value, $dict){       
        if(is_string($value)){
            $keys = $this->getDictKeys($value);
            foreach ($keys as $key){
                Verify::isTrue(isset($dict[$key]), "{$key} not specified");
            }
            foreach ($dict as $key=>$replace){
                $value = str_replace('{'.$key.'}', $replace, $value);
            }
            return $value;
        }else if(is_array($value)){
            foreach ($value as $k=>$v){
                $value[$k] = $this->replaceByDict($v, $dict);
            }
            return $value;
        }else{
            return $value;
        }
    }
    /**
     * 从字符串中获取模板变量
     * @param string $value
     * @return array
     */
    static function  getDictKeys($value){
        preg_match_all('/\{([0-9a-zA-Z\-\_]*?)\}/', $value, $params);
        $params += array(null, array());
        return $params[1];
    }
    protected $metas; 
    protected $singletons = array();
    protected $conf = null;
    protected $dict = array();
    protected $conf_file;
}
