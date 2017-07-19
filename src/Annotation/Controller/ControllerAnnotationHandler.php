<?php

namespace PhpBoot\Annotation\Controller;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Controller\ControllerContainer;

abstract class ControllerAnnotationHandler implements AnnotationHandler
{
    public function __construct(ControllerContainer $container, ControllerAnnotationHandler $parent=null){
        $this->container = $container;
        $this->parent = $parent;
    }

    /**
     * @var ControllerContainer
     */
    protected $container;

    /**
     * @var ControllerAnnotationHandler
     */
    protected $parent;
}