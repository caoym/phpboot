<?php

namespace  PhpBoot\Entity;

use PhpBoot\Metas\PropertyMeta;
use PhpBoot\Validator\Validator;

class EntityBuilder
{
    public function __construct($className)
    {
        $this->className = $className;
    }

    public function build($data, $validate = true)
    {
        $obj = new ($this->getClassName())();
        $vld = new Validator();
        foreach ($this->properties as $p){
            if(!$p->isOptional){
                $vld->rule('required', $p->name);
            }
            $vld->rule($p->validation, $p->name);
        }
        $vld->withData($data)->validate() or fail(
            new \InvalidArgumentException(
                json_encode(
                    $vld->errors(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            )
        );
        foreach ($this->properties as $p){
            if($p->builder && isset($properties[$p->name])){
                $properties[$p->name] = $p->builder->build($properties[$p->name]);
            }
        }

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