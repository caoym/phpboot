<?php
/***************************************************************************
 *
 * Copyright (c) 2013 . All Rights Reserved
 *
 **************************************************************************/
/**
 * $Id: Verify.php 57435 2014-12-21 15:04:22Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief
 */
namespace caoym\util;
/**
 * if(false) throw ;
 * @param boolen $var 判断条件
 * @param string $msg 异常消息
 * @throws Exception
 * @return unknown
 */
class Verify{
    /**
     * 如果判断不为true,抛出异常
     * @param boolean $var
     * @param string|Exception $msg
     * @param number $code
     * @throws \Exception
     * @return unknown
     */
	static public function isTrue($var, $msg = null)
    {
        if (!$var) {
            if($msg === null || is_string($msg)){
                Logger::warning($msg);
                throw new \Exception($msg);
            }else{
                Logger::warning($msg->__toString());
                throw $msg;
            }
        } else {
            return $var;
        }
    }
    /**
     * 
     * @param \Exception|string $e
     * @throws unknown
     */
    static public function e($e){
        if ($e === null || is_string($e)) {
            Logger::warning($e);
            throw new \Exception($e);
        } else {
            Logger::warning($e->__toString());
            throw $e;
        }
    }
}