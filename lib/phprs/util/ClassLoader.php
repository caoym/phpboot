<?php

/**
 * $Id: ClassLoader.php 58839 2015-01-17 16:18:55Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief 
 */
namespace phprs\util;

/**
 *  class loader
 * @author caoym
 *
 */
class ClassLoader{
   
    /**
     * @param unknown $path
     */
    static  public function addInclude($path){
        
        if (is_array($path)){
            self::$includes = array_unique(array_merge(self::$includes, $path));
        }else{
            self::$includes[] = $path;
            self::$includes = array_unique(self::$includes);
        }
    }
    /**
     * 
     * @param array $map
     * @param boolean $replace
     */
    static public function addClassMap($map, $replace=false){
        if($replace){
            self::$class_map = array_merge(self::$class_map, $map);
        }else{
            self::$class_map = array_merge($map, self::$class_map);
        }
    }
    /**
     * autoLoad
     * @param unknown $classname
     * @return void
     */
    static public function autoLoad($classname){
        if(array_key_exists($classname, self::$class_map)){
            $path = self::$class_map[$classname];
            require_once $path;
        }
        foreach(self::$includes as $path) {
            $path = $path . '/' . str_replace('\\', '/', $classname) . '.php';
            if (file_exists($path)) {
                self::$class_map[$classname] = $path;
                require_once $path;
                break;
            }
        }
    }

    static public $class_map=array();
    static public $includes=array();
}