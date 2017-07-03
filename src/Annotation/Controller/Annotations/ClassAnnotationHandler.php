<?php

namespace PhpBoot\Annotation\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\ControllerAnnotationHandler;

class ClassAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $ref = new \ReflectionClass($this->builder->getClassName());
        $this->builder->getClassName();

        $this->builder->setDescription($ann->description);
        $this->builder->setSummary($ann->summary);
        $this->builder->setFileName($ref->getFileName());
    }
}