<?php

namespace PhpBoot\Annotation\Entity;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\EntityContainer;

abstract class EntityAnnotationHandler implements AnnotationHandler
{
    public function __construct(EntityContainer $container, EntityAnnotationHandler $parent=null){
        $this->container = $container;
        $this->parent = $parent;
    }

    /**
     * @var EntityContainer
     */
    protected $container;

    /**
     * @var EntityAnnotationHandler
     */
    protected $parent;

}