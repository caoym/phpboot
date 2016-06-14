<?php

/**
 * $Id: RouterWithCache.php 57516 2014-12-23 05:44:20Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief RouterCache
 */

namespace caoym\phprs;
use caoym\util\FileExpiredChecker;
use caoym\util\Logger;
/**
 * 支持缓存的Router
 * 初始化Router需要解析类和方法的注释, 通过@标记绑定接口的参数, 此过程非常耗时, 缓
 * 存可以将解析后的结果保留, 包括api容器, 调用参数的绑定顺序等, 避免此消耗.
 * 
 * 由于Router初始化时并不会创建API实例, 而是在API被调用时才创建, 所以缓存不会保存
 * API实例, 此特性有助于简化API的设计, API会在每个请求中重新初始化
 * @author caoym
 */
class RouterWithCache
{
    /**
     * @return void
     */
    function __construct(){

        $ok=false;
        if($this->factory->getConfFile() ===null){
            $key = 'phprs_route3_'.sha1(serialize($this->factory->getConf()));
        }else{
            $key = 'phprs_route3_'.sha1($this->factory->getConfFile());
        }
        $this->impl = $this->cache->get($key, $ok);
        if($ok && is_object($this->impl)){
            Logger::info("router loaded from cache");
            return ;
        }
        $this->impl = $this->factory->create('caoym\\phprs\\Router');
        //缓存过期判断依据
        //检查接口文件是否有修改\新增
        $check_files = array_values($this->impl->getApiFiles());
        $check_dirs=array();
        foreach($check_files as $file){
            if(is_file($file)){
                $check_dirs[] = dirname($file);
            }
        }
        $check_files = array_merge($check_files, $check_dirs);
        $check_files[]=$this->factory->getConfFile();

        $this->cache->set($key, $this->impl, 0, new FileExpiredChecker($check_files)); //接口文件或者配置文件修改
    }
     /**
     * 调用路由规则匹配的api
     * @param Request $request
     * @param Response $respond
     * @return mixed
     */
    function __invoke($request=null, &$respond=null, $catch_exceptions=true){
        return $this->impl->__invoke($request, $respond, $catch_exceptions);
    }

    /** @property({"default":"@caoym\util\Cache"})  */
    private $cache;
    /** @inject("ioc_factory") */
    private $factory;
    
    private $impl;
}