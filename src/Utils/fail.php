<?php
use  PhpBoot\Utils\Logger;

if (! function_exists('fail')) {
    /**
     * 抛出异常, 并记录日志
     * @param string|Exception $error
     * @param array $context
     * @param string $level "error"|"warning"|"info"|"debug"|null
     * @throws Exception
     */
    function fail($error = '', $context=[], $level='warning')
    {
        if(is_object($context)){
            $context = get_object_vars($context);
        }
        if($error instanceof \Exception){
            $e = $error;
        }else{
            $e = new \Exception($error);
        }
        $trace = $e->getTrace();

        if($e->getFile() == __FILE__){
            $file = $trace[0]['file'];
            $line = $trace[0]['line'];
        }else{
            $file = $e->getFile();
            $line = $e->getLine();
        }
        if($level){
           Logger::$level($error, $context +['@file'=>$file, '@line'=>$line]);
        }
        throw $e;
    }
}