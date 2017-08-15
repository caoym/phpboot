<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Utils\AnnotationParams;

class PathAnnotationHandler
{

    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ControllerContainer $container, $ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        $container->setUriPrefix($params->getParam(0, ''));
    }
}