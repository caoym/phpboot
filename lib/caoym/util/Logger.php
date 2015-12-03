<?php
/***************************************************************************
 *
* Copyright (c) 2013 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: Logger.php 58820 2015-01-16 16:29:33Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief
 */
namespace caoym\util;
/**
 * 简单的日志输出, 方便应用替换自己的日志实现
 * @author caoym
 * 
 */
class Logger
{
    /**
     * echo输出
     */
    static $to_echo;
    
    /**
     * 忽略输出
     */
    static $to_void;
    /**
     * trigger_error 输出
     * @var callable
     */
    static $to_php_log;
    /**
     * 输出到哪, 默认为Logger::$to_php_log
     * @var callable
     */
    static $writer;
   
    const DEBUG=1; 
    const INFO=2;
    const WARNING=4;
    const ERROR=8;
    /**
     * debug log
     * @param string $msg
     * @return void
     */
    public static function debug($msg){
        call_user_func(Logger::$writer, self::DEBUG, $msg);
    }
    /**
     * info log
     * @param string $msg
     * @return void
     */
    public static function info($msg){
        call_user_func(Logger::$writer, self::INFO, $msg);
    }
    /**
     * warning log
     * @param string $msg
     * @return void
     */
    public static function warning($msg){
        call_user_func(Logger::$writer, self::WARNING, $msg);
    }
    /**
     * error log
     * @param string $msg
     * @return void
     */
    public static function error($msg){
        call_user_func(Logger::$writer, self::ERROR, $msg);
    } 
    /**
     * init
     * @return void
     */
    public static function init(){
        Logger::$to_echo = function ($level, $message){
            $titles = array(
                Logger::DEBUG => '==DEBUG==',
                Logger::INFO => '==INFO==',
                Logger::WARNING => '==WARNING==',
                Logger::ERROR => '==ERROR==',
            );
            echo $titles[$level].' '.$message."<br>\n";
        };
        
        Logger::$to_php_log = function ($level, $message)
        {
            $titles = array(
                Logger::DEBUG => E_USER_NOTICE,
                Logger::INFO => E_USER_NOTICE,
                Logger::WARNING => E_USER_WARNING,
                Logger::ERROR => E_USER_ERROR,
            );
            $caller = debug_backtrace()[2];
            trigger_error($message.' in '.$caller['file'].' on line '.$caller['line'].''."\n<br />", $titles[$level]);
        };
        
        Logger::$to_void = function ($level, $message){
        };
        if(Logger::$writer === null){
            Logger::$writer = Logger::$to_php_log;
        }
        
    }  
}

Logger::init();

