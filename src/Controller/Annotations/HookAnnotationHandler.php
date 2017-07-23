<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\HookInterface;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class HookAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ControllerContainer $container, $ann)
    {
        if(!$ann->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} should be used with parent route");
            return;
        }
        $target = $ann->parent->name;
        $route = $container->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent route");
            return ;
        }
        $params = new AnnotationParams($ann->description, 2);
        count($params)>0 or \PhpBoot\abort("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target require at least one param, 0 given");
        $className = $params[0];
        $className = TypeHint::normalize($className, $container->getClassName());
        is_subclass_of($className, HookInterface::class) or \PhpBoot\abort("$className is not a HookInterface on the annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target");
        $route->addHook($className);
    }
}