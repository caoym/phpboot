<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Validator\Validator;

class ValidateAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     */
    public function __invoke(ControllerContainer $container, $ann)
    {
        if(!$ann->parent || !$ann->parent->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} should be used with parent parent");
            return;
        }
        $target = $ann->parent->parent->name;
        $route = $container->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent parent");
            return ;
        }
        $params = new AnnotationParams($ann->description, 2);

        count($params)>0 or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target require 1 param, {$params->count()} given"));

        if($ann->parent->name == 'param'){
            list($paramType, $paramName, $paramDoc) = ParamAnnotationHandler::getParamInfo($ann->parent->description);

            $paramMeta = $route->getRequestHandler()->getParamMeta($paramName);
            if($params->count()>1){
                $paramMeta->validation = [$params[0], $params[1]];
            }else{
                $paramMeta->validation = $params[0];
                if($paramMeta->validation) {
                    $v = new Validator();
                    $v->rule($paramMeta->validation, $paramMeta->name);
                    if ($v->hasRule('optional', $paramMeta->name)) {
                        $paramMeta->isOptional = true;
                    }
                }
            }

            return;
        }
        Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent parent");
    }
}