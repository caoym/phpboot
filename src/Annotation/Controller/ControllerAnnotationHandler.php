<?php

namespace PhpBoot\Annotation\Controller;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Controller\ControllerBuilder;

abstract class ControllerAnnotationHandler implements AnnotationHandler
{
    public function __construct(ControllerBuilder $builder, ControllerAnnotationHandler $parent=null){
        $this->builder = $builder;
        $this->parent = $parent;
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