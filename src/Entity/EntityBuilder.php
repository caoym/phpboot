<?php

namespace  PhpBoot\Entity;

use PhpBoot\Metas\PropertyMeta;

class EntityBuilder
{
    public function __construct($className)
    {
        $this->className = $className;
    }

    public function build()
    {

    }

    public function getProperty($target){
        if(array_key_exists($target, $this->properties)){
            return $this->properties[$target];
        }
        return null;
    }
    public function setProperty($target, PropertyMeta $meta){
        $this->properties[$target] = $meta;
    }
    /**
     * @return PropertyMeta[]
     */
    public function getProperties(){
        return $this->properties;
    }
    public function getDoc(){
        return $this->doc;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    /**
     * @var PropertyMeta[]
     */
    private $properties=[];

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $doc='';

    /**
     * @var string
     */
    private $fileName;
}