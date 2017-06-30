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
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
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
    private $description='';
    /**
     * @var string
     */
    private $summary='';

    /**
     * @var string
     */
    private $fileName;
}