<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class ThrowsAnnotationHandler
{

    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ControllerContainer $container, $ann)
    {
        if(!$ann->parent){
            //Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} should be used with parent route");
            return;
        }
        $target = $ann->parent->name;
        $route = $container->getRoute($target);
        if(!$route){
            //Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent route");
            return ;
        }
        $params = new AnnotationParams($ann->description, 2);
        count($params)>0 or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target require at least one param, {$params->count()} given"));

        $type = TypeHint::normalize($params[0], $container->getClassName()); // TODO 缺少类型时忽略错误
        $doc = $params->getRawParam(1, '');

        $route->getExceptionHandler()->addExceptions($type, $doc);
    }
}