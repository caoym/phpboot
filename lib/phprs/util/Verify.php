<?php

/**
 * $Id: Verify.php 57435 2014-12-21 15:04:22Z caoyangmin $
 * @author caoym(caoyangmin@gmail.com)
 * @brief
 */
namespace phprs\util;
use phprs\util\Logger;

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