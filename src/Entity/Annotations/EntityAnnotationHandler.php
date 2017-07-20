<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Entity\EntityContainerBuilder;

abstract class EntityAnnotationHandler implements AnnotationHandler
{
    /**
     * EntityAnnotationHandler constructor.
     * @param EntityContainerBuilder $builder
     * @param EntityContainer $container
     */
    public function __construct(EntityContainerBuilder $builder, EntityContainer $container){
        $this->container = $container;
        $this->builder = $builder;
    }

    /**
     * @var EntityContainer
     */
    protected $container;
    protected $builder;
}