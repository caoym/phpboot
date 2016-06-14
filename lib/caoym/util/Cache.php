<?php

/**
 * $Id: Cache.php 57516 2014-12-23 05:44:20Z caoyangmin $
 *
 * @author caoyangmin(caoyangmin@baidu.com)
 *         @brief Cache
 */
namespace caoym\util;

/**
 * 
 * @author caoym
 */
class Cache extends CheckableCache
{
    public function __construct()
    {
        if (!function_exists('apc_fetch') || !function_exists('apc_store') || !function_exists('apc_delete')) {
            parent::__construct(new FileCache(), $this->tag);
        }else{
            parent::__construct(new ApcCache(), $this->tag);
        }
    }
    /** @property */
    private $tag = "";
}
