<?php

namespace PhpBoot\Controller\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;

class ClassAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ControllerContainer $container, $ann)
    {
        $ref = new \ReflectionClass($container->getClassName());
        $container->getClassName();

        $container->setDescription($ann->description);
        $container->setSummary($ann->summary);
        $container->setFileName($ref->getFileName());
    }
}