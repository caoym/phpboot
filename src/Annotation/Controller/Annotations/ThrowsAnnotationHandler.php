<?php

namespace PhpBoot\Annotation\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Controller\ControllerAnnotationHandler;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class ThrowsAnnotationHandler extends ControllerAnnotationHandler
{

    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        if(!$ann->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->container->getClassName()} should be used with parent route");
            return;
        }
        $target = $ann->parent->name;
        $route = $this->container->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->container->getClassName()}::$target should be used with parent route");
            return ;
        }
        $params = new AnnotationParams($ann->description, 2);
        count($params)>0 or fail(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$this->container->getClassName()}::$target require at least one param, {$params->count()} given"));

        $type = TypeHint::normalize($params[0], $this->container->getClassName()); // TODO 缺少类型时忽略错误
        $doc = $params->getRawParam(1, '');

        $route->getExceptionHandler()->addExceptions($type, $doc);
    }
}