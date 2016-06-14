<?php
/**
 * $Id: AutoClassLoader.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief 
 */
namespace phprs\util;

/**
 * 反序列化时自动加载类
 * @author caoym
 *
 */
class AutoClassLoader{
    /**
     * 反序列化时被调用
     */
    function __wakeup(){
        ClassLoader::addClassMap($this->map, false);
    }
    /**
     * 添加类文件映射
     * @param unknown $name
     * @param unknown $file
     */
    public function addClass($name, $file){
        $this->map[$name] = $file;
        ClassLoader::addClassMap($this->map, false);
    }
    /**
     * 
     * @return array:
     */
    public function getClasses(){
        return $this->map;
    }
    private $map=array();
}