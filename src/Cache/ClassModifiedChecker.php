<?php

namespace PhpBoot\Cache;

class ClassModifiedChecker extends FileModifiedChecker
{
    function __construct($className){
        $class = new \ReflectionClass($className);
        $files = [];

        if($class->getFileName()){
            $files[] = $class->getFileName();
            self::getParentFileName($class, $files);
        }
        parent::__construct($files);
    }

    static public function getParentFileName(\ReflectionClass $class, array &$files)
    {
        $parent = $class->getParentClass();
        if(!$parent){
            return;
        }
        if($parent->getFileName()){
            $files[] = $parent->getParentClass();
            self::getParentFileName($parent, $files);
        }
        foreach ($class->getInterfaces() as $interface){
            if($interface->getFileName()){
                $files[] = $interface->getFileName();
                self::getParentFileName($interface, $files);
            }
        }
    }
}