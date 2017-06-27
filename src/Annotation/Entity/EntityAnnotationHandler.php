<?php

namespace PhpBoot\Annotation\Entity;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Entity\EntityBuilder;

class EntityAnnotationHandler implements AnnotationHandler
{
    public function __construct(EntityBuilder $builder, EntityAnnotationHandler $parent=null){
        $this->builder = $builder;
        $this->parent = $parent;
    }

    /**
     * @param string $type
     * @param string $target the name of the class or method or property
     * @param string $name tag name
     * @param string $value
     * @return boolean
     */
    public function handle($type, $target, $name, $value)
    {
        if ($type == self::TYPE_CLASS){
            $this->handleClass($target, $name, $value);
        }elseif ($type == self::TYPE_METHOD){
            $this->handleMethod($target, $name, $value);
        }elseif ($type == self::TYPE_PROPERTY){
            $this->handleProperty($target, $name, $value);
        }
    }

    protected function handleClass($target, $name, $value)
    {

    }

    protected function handleMethod($target, $name, $value)
    {

    }

    protected function handleProperty($target, $name, $value)
    {

    }

    /**
     * @var EntityBuilder
     */
    protected $builder;

    /**
     * @var EntityAnnotationHandler
     */
    protected $parent;
}