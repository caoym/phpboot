<?php

namespace PhpBoot\Controller\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\Annotations\ControllerAnnotationHandler;
use PhpBoot\Utils\AnnotationParams;

class PathAnnotationHandler extends ControllerAnnotationHandler
{

    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        $this->container->setPathPrefix($params->getParam(0, ''));
    }
}