<?php

namespace PhpBoot\Annotation\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Controller\ControllerAnnotationHandler;
use PhpBoot\Controller\HookInterface;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class HookAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        if(!$ann->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()} should be used with parent route");
            return;
        }
        $target = $ann->parent->name;
        $route = $this->builder->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()}::$target should be used with parent route");
            return ;
        }
        $params = new AnnotationParams($ann->description, 2);
        count($params)>0 or fail("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()}::$target require at least one param, 0 given");
        $className = $params[0];
        $className = TypeHint::normalize($className, $this->builder->getClassName());
        is_subclass_of($className, HookInterface::class) or fail("$className is not a HookInterface on the annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()}::$target");
        $route->addHook($className);
    }
}