<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: FileCache.php 57516 2014-12-23 05:44:20Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 */
namespace caoym\util;
/**
 * 基于文件实现的缓存,  类似APC
 * @author caoym
 */
class FileCache implements KVCatchInterface
{
    /**
     * @param string cache_dir 缓存保存的路径, 为空则使用sys_get_temp_dir取得的路径
     */
    public  function __construct($cache_dir=null) {
        if($cache_dir===null){
            $this->cache_dir = sys_get_temp_dir().'/caoym_temp';
        }else{
            $this->cache_dir = $cache_dir;
        }
    }
    /**
     * 设置key
     * @param string $key
     * @param mixed $var
     * @param int $ttl TODO: 暂不支持
     * @return void
     */
    public function set($key, $var, $ttl=0){
        Verify::isTrue(is_dir($this->cache_dir) || @mkdir($this->cache_dir, 0777, true));
        $path = $this->cache_dir.'/'.sha1($key);
        return SaftyFileWriter::write($path, serialize($var));
    }
    /**
     * 删除key
     * @param string $key
     * @return void
     */
    public function del($key){
        $path = $this->cache_dir.'/'.sha1($key);
        return @unlink($path);
    }
    /**
     * 模拟apc, 只在没有apc的开发环境使用
     * @param string $key
     * @param boolean $succeeded
     * @return boolean
     */
    public function get($key, &$succeeded=null){
        $succeeded = false;
        $path = $this->cache_dir.'/'.sha1($key);
        if(!file_exists($path)){
            return false;
        }
        $res = file_get_contents($path);
        if($res === false){
            return  false;
        }
        $succeeded = true;
        return unserialize($res);
    }
    
    private $cache_dir;
}
