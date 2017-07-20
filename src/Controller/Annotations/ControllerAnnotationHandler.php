<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Controller\ControllerContainer;

abstract class ControllerAnnotationHandler implements AnnotationHandler
{
    public function __construct(ControllerContainer $container){
        $this->container = $container;
    }
    /**
     * @var ControllerContainer
     */
    protected $container;

}