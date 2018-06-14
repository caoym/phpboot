<?php

namespace PhpBoot\Console\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Console\ConsoleContainer;

class ClassAnnotationHandler
{
    /**
     * @param ConsoleContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @throws \ReflectionException
     */
    public function __invoke(ConsoleContainer $container, $ann)
    {
        $ref = new \ReflectionClass($container->getClassName());
        $container->setDescription($ann->description);
        $container->setSummary($ann->summary);

    }
}