<?php
namespace PhpBoot;
use PhpBoot\DB\DB;
use PhpBoot\ORM\ModelWithClass;
use PhpBoot\ORM\ModelWithObject;
use PhpBoot\Utils\Logger;

if (! function_exists('PhpBoot\abort')) {
    /**
     * 抛出异常, 并记录日志
     * @param string|\Exception $error
     * @param array $context
     * @param string $level "error"|"warning"|"info"|"debug"|null
     * @throws \Exception
     */
    function abort($error = '', $context=[], $level='warning')
    {
        if(is_object($context)){
            $context = get_object_vars($context);
        }
        if($error instanceof \Exception){
            $e = $error;
            $message = "exception '".get_class($error)."' with message {$error->getMessage()}";
        }else{
            $e = new \RuntimeException($error);
            $message = $error;
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
           Logger::$level($message, $context +['@file'=>$file, '@line'=>$line]);
        }
        throw $e;
    }

}

if (!function_exists('PhpBoot\model')) {

    /**
     * @param DB $db
     * @param @param object
     * @return ModelWithObject
     */
    function model(DB $db, $entity)
    {
        return $db->getApp()->make(ModelWithObject::class, ['db'=>$db, 'entity'=>$entity]);
    }

    /**
     * @param DB $db
     * @param @param string $entity
     * @return ModelWithClass
     */
    function models(DB $db, $entity)
    {
        if(is_object($entity)){
            return $db->getApp()->make(ModelWithObject::class, ['db'=>$db, 'entity'=>$entity]);
        }else{
            return $db->getApp()->make(ModelWithClass::class, ['db'=>$db, 'entityName'=>$entity]);
        }
    }
}