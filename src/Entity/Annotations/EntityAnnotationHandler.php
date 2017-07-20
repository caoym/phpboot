<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Entity\EntityContainer;

abstract class EntityAnnotationHandler implements AnnotationHandler
{
    public function __construct(EntityContainer $container){
        $this->container = $container;
    }

    /**
     * @var EntityContainer
     */
    protected $container;
}