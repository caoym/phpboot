<?php

namespace PhpBoot\Console\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Console\ConsoleContainer;
use PhpBoot\Utils\AnnotationParams;

class CommandNameAnnotationHandler
{
    /**
     * @param ConsoleContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ConsoleContainer $container, $ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        $container->setModuleName($params->getParam(0, ''));
    }
}