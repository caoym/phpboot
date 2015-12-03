<?php
/***************************************************************************
 *
* Copyright (c) 2014 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: KVCatchInterface.php 57516 2014-12-23 05:44:20Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 */
namespace caoym\util;

interface KVCatchInterface
{
    /**
     * 设置key
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $var, $ttl);
    /**
     * 删除key
     * @param string $key
     * @return boolean
     */
    public function del($key);
    /**
     * get key
     * @param string $key
     * @param boolean $succeeded
     * @return mixed
     */
    public function get($key, &$succeeded);
    
}