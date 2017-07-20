<?php

namespace PhpBoot\Controller\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\Annotations\ControllerAnnotationHandler;

class ClassAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $ref = new \ReflectionClass($this->container->getClassName());
        $this->container->getClassName();

        $this->container->setDescription($ann->description);
        $this->container->setSummary($ann->summary);
        $this->container->setFileName($ref->getFileName());
    }
}