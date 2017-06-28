<?php

namespace PhpBoot\Annotation\Entity;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationHandler;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\EntityBuilder;

abstract class EntityAnnotationHandler implements AnnotationHandler
{
    public function __construct(EntityBuilder $builder, EntityAnnotationHandler $parent=null){
        $this->builder = $builder;
        $this->parent = $parent;
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