<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Entity\EntityContainerBuilder;

abstract class ControllerAnnotationHandler implements AnnotationHandler
{
    public function __construct(EntityContainerBuilder $entityBuilder, ControllerContainer $container){
        $this->container = $container;
        $this->entityBuilder = $entityBuilder;
    }
    /**
     * @var ControllerContainer
     */
    protected $container;

    /**
     * @var EntityContainerBuilder
     */
    public $entityBuilder;

}