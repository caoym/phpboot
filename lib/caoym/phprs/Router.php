<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: Router.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief Router
 */
namespace caoym\phprs;
use caoym\util\HttpRouterEntries;
use caoym\util\Verify;
use caoym\util\AutoClassLoader;
use caoym\util\Logger;
use caoym\util\exceptions\NotFound;
use caoym\util\exceptions\BadRequest;
use caoym\util\exceptions\Forbidden;

/**
 * 结果保留在内存
 * @author caoym
 */
class BufferedRespond extends Response{
    /**
     * flush 不直接输出
     * @see \caoym\phprs\Response::flush()
	 * @param string limit 现在输出的项目
	 * @param callable $func 输出方法
	 * @return void
     */
    public function flush($limit=null, $func=null)
    {
        return ;
    }
}
/**
 * restful api 路由
 * 加载API类文件, 通过API类的@annotation获取路由规则信息, 当请求到来时, 调用匹配的
 * API类方法.
 * 
 * 通常每一个请求只对应到一个最严格匹配的API接口, 所谓"最严格匹配",比如:
 * API1 接口 =>  url: /apis/test
 * API2 接口 =>  url: /apis
 * 那么当请求"/apis/test/123/" 最严格匹配的接口是API1
 * 
 * 如果需要让一个请求经过多个API调用, 比如有时候会需要一个统一验证的接口, 让所有请
 * 求先通过验证接口, 再调用其他接口. 此时可以通过Router的hooks属性, 设置一组hook实
 * 现. hook其实和普通的接口一样, 只是在hooks中指定后, 执行行为将有所不同: 请求会按
 * 优先级逐个经过hook, 只要匹配, hook的方法就会被调用, 直到最后调用普通的API
 * 
 * 通过@return({"break", true})停止请求链路
 * 
 * @author caoym
 */
class Router
{
    private $class_loader; //用于确保反序列化时自动加载类文件
    /**
     * 
     */
    function __construct( ){
        //AutoClassLoader确保序列化后自动加载类文件
        $this->class_loader = new AutoClassLoader();
        //TODO: 支持名字空间
        //TODO: 支持多路径多类名
        $this->load($this->api_path, $this->apis, $this->api_method);
        //允许通过接口访问api信息
        if($this->export_apis){
            $this->loadApi($this->routes, __DIR__.'/apis/ApiExporter.php', 'caoym\phprs\apis\ApiExporter');
        }
    }
    
    /**
     * 获取api文件列表
     * @return array
     */
    public function getApiFiles(){
        return $this->class_loader->getClasses();
    }
    /**
     * 调用路由规则匹配的api
     * @param Request $request
     * @param Response $respond
     * @param boolean $catch_exceptions whether catch all exceptions of invoke,
     * @return void
     */
    public function __invoke($request=null, &$respond=null, $catch_exceptions=true){
        if($request === null){
            $request = new Request(null,$this->url_begin);
        }
        if($respond==null){
            $respond = new Response();
        }
        $request['$.router'] = $this;
        //先按配置的顺序调用hook
        foreach ($this->hook_routes as $hook){
            $res = new BufferedRespond();
            if(!$this->invokeRoute($hook, $request, $res)){
                continue;
            }
            $respond->append($res->getBuffer());
            $break = false;
            $respond->flush('break', function($var)use(&$break){$break = $var;});
            if($break){
                Logger::info("invoke break");
                $respond->flush();
                return;
            }
        }
        $res = new BufferedRespond();
        if(!$catch_exceptions){
            Verify::isTrue($this->invokeRoute($this->routes, $request, $res), new NotFound());
            $respond->append($res->getBuffer());
            $respond->flush();
        }else{
            $err = null;
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            //执行请求
            try {
                Verify::isTrue($this->invokeRoute($this->routes, $request, $res), new NotFound());
                $respond->append($res->getBuffer());
                $respond->flush();
            }catch (NotFound $e) {
                header($protocol . ' 404 Not Found');
                $err = $e;
            }catch (BadRequest $e) {
                header($protocol . ' 400 Bad Request');
                $err = $e;
            }catch (Forbidden $e){
                header($protocol . ' 403 Forbidden');
                $err = $e;
            }
            if($err){
                header("Content-Type: application/json; charset=UTF-8");
                $estr = array(
                    'error' => get_class($err),
                    'message' => $err->getMessage(),
                );
                echo json_encode($estr);
            }
        }
    }
   /**
    * 
    * @param string|array $api_path
    * @param string $apis
    * @param string $api_method
    */
    public function load($api_path, $apis=null , $api_method=null){
        if(is_string($api_path)){
            $api_paths = array($api_path);
        }else{
            $api_paths = $api_path;
        }
        Verify::isTrue(is_array($api_paths), 'invalid param');
    
        foreach ($api_paths as $api_path){
            $this->loadRoutes($this->routes, $api_path, $apis, $api_method);
            foreach ($this->hooks as $hook) {
                $hook_route=array();
                $this->loadRoutes($hook_route, $api_path.'/hooks', $hook, null);
                $this->hook_routes[] = $hook_route;
            }
        }
    }
    /**
     * @return array
     */
    public function getRoutes(){
        return $this->routes;
    }
    /**
     * @return array
     */
    public function getHooks(){
        return $this->hook_routes;
    }
    
    /**
     * 调用路由规则匹配的api
     * @param array $routes 路由规则
     * @param unknown $request
     * @param unknown $respond
     * @return boolean 是否有匹配的接口被调用
     */
    private function invokeRoute($routes, $request, &$respond){
        $method = $request['$._SERVER.REQUEST_METHOD'];
        $path = $request['$.path'];
        $uri = $request['$._SERVER.REQUEST_URI'];
        list(,$params) = explode('?', $uri)+array( null,null );
	    $params = is_null($params)?null:explode('&', $params);
	    
        Logger::debug("try to find route $method ".$uri);
        $match_path = array();
        if(isset($routes[$method])){
            if(($api = $routes[$method]->findByArray($path,$params,$match_path)) !== null){
                Logger::debug("invoke $uri => {$api->getClassName()}::{$api->getMethodName()}");
                $api($request, $respond);
                return true;
            }
        }
         
        if(!isset($routes['*'])){
            return false;
        }
        if(($api = $routes['*']->find($uri, $match_path)) === null){
            return false;
        }
        Logger::debug("invoke $uri => {$api->getClassName()}::{$api->getMethodName()}");
        $api($request, $respond);
        
        return true;
    }
    /**
     * @param string $apis_dir
     * @param string $class
     * @param string $method
     * @return void
     */
    public function addRoutes($apis_dir, $class=null, $method=null){
        $this->loadRoutes($this->routes, $apis_dir, $class, $method);
    }
   /**
    * 遍历API目录生成路由规则
    * @param object $factory
    * @param string $apis_dir
    * @param string $class
    * @param string $method
    * @param array $skipclass 需要跳过的类名
	* @return Router
    */
    private function loadRoutes(&$routes, $apis_dir, $class, $method){
        Logger::info("attempt to load router $apis_dir");
        $dir = null;
      
        if(is_dir($apis_dir) && $class === null){
            $apis_dir=$apis_dir.'/';
            Verify::isTrue(is_dir($apis_dir), "$apis_dir not a dir");
            $dir = @dir($apis_dir);
            Verify::isTrue($dir !== null, "open dir $apis_dir failed");
            $geteach = function ()use($dir){
                $name = $dir->read();
                if(!$name){
                    return $name;
                }
                return $name;
            };
        }else{
            if(is_file($apis_dir)){
                $files = array($apis_dir);
                $apis_dir = '';
            }else{
                $apis_dir=$apis_dir.'/';
                if(is_array($class)){
                    foreach ($class as &$v){
                        $v .= '.php';
                    }
                    $files = $class;
                }else{
                    $files = array($class.'.php');
                }
            }
            $geteach = function ()use(&$files){
                $item =  each($files);
                if($item){
                    return $item[1];
                }else{
                    return false;
                }
            };
        }
        while( !!($entry = $geteach()) ){
            $path = $apis_dir. str_replace('\\', '/', $entry);
            if(is_file($path) && substr_compare ($entry, '.php', strlen($entry)-4,4,true) ==0){
                $class_name = substr($entry, 0, strlen($entry)-4);
                $this->loadApi($routes, $path, $class_name, $method);
            }else{
                Logger::debug($path.' ignored');
            }
        }
        if($dir !== null){
            $dir->close();
        }
        Logger::info("load router $apis_dir ok");
        return $routes;
    }
    /**
     * 加载api类
     * @param array $routes
     * @param string $class_file
     * @param string $class_name
     * @param string $method
     * @return void
     */
    private function loadApi(&$routes, $class_file, $class_name, $method=null){
        Verify::isTrue(is_file($class_file), $class_file.' is not an exist file');
        Logger::debug("attempt to load api: $class_name, $class_file");
        
        $this->class_loader->addClass($class_name, $class_file);
        $api = null;
        if ($this->ignore_load_error){
            try {
                $api = $this->factory->create('caoym\\phprs\\Container', array($class_name, $method), null, null);
            } catch (\Exception $e) {
                Logger::warning("load api: $class_name, $class_file failed with ".$e->getMessage());
                return ;
            }
        }else{
            $api = $this->factory->create('caoym\\phprs\\Container', array($class_name, $method), null, null);
        }
        
        foreach ($api->routes as $http_method=>$route){
            if(!isset($routes[$http_method])){
                $routes[$http_method] = new HttpRouterEntries();
            }
            $cur = $routes[$http_method];
            foreach ($route as $entry){
                $realpath = preg_replace('/\/+/', '/', '/'.$entry[0]);
                Verify::isTrue($cur->insert($realpath, $entry[1]), "repeated path $realpath");
                Logger::debug("api: $http_method $realpath => $class_name::{$entry[1]->method_name} ok");
            }
        }
        Logger::debug("load api: $class_name, $class_file ok");
    }
    private $routes=array();
    private $hook_routes=array();
    
    
    /** @inject("ioc_factory") */
    public $factory;
    /** @property */
    private $api_path='apis';
    /** @property */
    private $apis=null;
    /** @property */
    private $api_method=null;
    
    private $api_root=null;
    /** @property
     * 指定hook的类名, 从优先级高到低
     * 如果一条规则匹配多个hook, 则优先级高的先执行, 再执行优先级低的
     */
    private $hooks=array();
    /**
     * @property
     * 是否允许通过接口获取api信息
     */
    private $export_apis=false;
    /**
     * 用于匹配路由的url偏移
     * @property
     */
    public $url_begin=0;
    /**
     * @property 忽略类加载时的错误，只是跳过出错的接口。否则抛出异常。
     */
    public $ignore_load_error=true;
    
}
