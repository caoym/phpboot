<?php

/**
 * $Id: ApcCache.php 57516 2014-12-23 05:44:20Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 */
namespace phprs\util;

/**
 * apc 缓存
 * @author caoym
 *
 */
class ApcCache implements KVCatchInterface
{
    /**
     * get 
     * @param string $key
     * @param boolean $succeeded
     * @return mixed The stored variable or array of variables on success; false on failure
     */
    public function get($key, &$succeeded)
    {
        return apc_fetch($key, $succeeded);
    }
    /**
     * @param string $key 
     * @param string $var 
     * @param int $ttl
     * @return boolean bool Returns true on success or false on failure. Second syntax returns array with error keys.
     */
    public function set($key, $var, $ttl)
    {
        return apc_store($key, $var, $ttl);
    }
    /**
     * @param string $key 
     * @return boolean
     */
    public function del($key)
    {
        return apc_delete($key);
    }
}
