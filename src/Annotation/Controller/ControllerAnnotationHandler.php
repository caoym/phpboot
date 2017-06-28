<?php

namespace PhpBoot\Annotation;

use PhpBoot\Container\ControllerBuilder;

class ControllerAnnotationHandler implements AnnotationHandler
{
    public function __construct(ControllerBuilder $builder, ControllerAnnotationHandler $parent=null){
        $this->builder = $builder;
        $this->parent = $parent;
    }

    /**
     * @param string $type TYPE_CLASS/TYPE_METHOD/TYPE_PROPERTY
     * @param string $target the name of the class or method or property
     * @param AnnotationBlock $block
     * @return void
     */
    public function handle($type, $target, $block)
    {
        if ($type == self::TYPE_CLASS){
            $this->handleClass($target, $block);
        }elseif ($type == self::TYPE_METHOD){
            $this->handleMethod($target, $block);
        }elseif ($type == self::TYPE_PROPERTY){
            $this->handleProperty($target, $block);
        }
    }
    /**
     * @param string $target the name of the class or method or property
     * @param AnnotationBlock $block
     */
    protected function handleClass($target, $block){

    }
    /**
     * @param string $target the name of the class or method or property
     * @param AnnotationBlock $block
     */
    protected function handleMethod($target, $block){

    }
    /**
     * @param string $target the name of the class or method or property
     * @param AnnotationBlock $block
     */
    protected function handleProperty($target, $block){

    }

    /**
     * @var ControllerBuilder
     */
    protected $builder;

    /**
     * @var ControllerAnnotationHandler
     */
    protected $parent;
}