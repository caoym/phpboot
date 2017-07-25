<?php

namespace  PhpBoot\Entity;

use PhpBoot\Metas\PropertyMeta;
use PhpBoot\Validator\Validator;

class EntityContainer implements TypeContainerInterface
{
    public function __construct($className)
    {
        $this->className = $className;
    }

    public function make($data, $validate = true)
    {
        is_array($data) or \PhpBoot\abort(new \InvalidArgumentException("array is required by param 0"));
        $className = $this->getClassName();
        $obj = new $className();
        $vld = new Validator();
        foreach ($this->properties as $p){
            if($p->container && isset($data[$p->name])){
                $data[$p->name] = $p->container->make($data[$p->name], $validate);
            }
            if(!$p->isOptional){
                $vld->rule('required', $p->name);
            }
            if($p->validation){
                if(is_array($p->validation)){
                    $vld->rule($p->validation[0], $p->name.'.'.$p->validation[1]);
                }else{
                    $vld->rule($p->validation, $p->name);
                }
            }
        }
        if($validate){
            $vld->withData($data)->validate() or \PhpBoot\abort(
                new \InvalidArgumentException(
                    json_encode(
                        $vld->errors(),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    )
                )
            );
        }

        foreach ($this->properties as $p){
            if(isset($data[$p->name])){
                $obj->{$p->name} = $data[$p->name];
            }
        }
        return $obj;

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