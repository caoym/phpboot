<?php

namespace PhpBoot\Annotation\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Annotation\Controller\ControllerAnnotationHandler;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;

class BindAnnotationHandler extends ControllerAnnotationHandler
{
    /**
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function handle($ann)
    {
        if(!$ann->parent || !$ann->parent->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()} should be used with parent param/return");
            return;
        }
        $target = $ann->parent->parent->name;
        $route = $this->builder->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()}::$target should be used with parent param/return");
            return ;
        }

        $params = new AnnotationParams($ann->description, 2);

        $params->count()>0 or fail(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$this->builder->getClassName()}::$target require 1 param, {$params->count()} given"));

        $handler = $route->getResponseHandler();

        if ($ann->parent->name == 'return'){
            foreach ($handler->getMappings() as $mapping){
                $mapping->source = $params->getParam(0);
            }
        }elseif($ann->parent->name == 'param'){
            list($paramType, $paramName, $paramDoc) = ParamAnnotationHandler::getParamInfo($ann->parent->description);

            $paramMeta = $route->getRequestHandler()->getParamMeta($paramName);
            if($paramMeta->isPassedByReference){
                //输出绑定
                $handler->setMapping(
                    $params[0],
                    new ReturnMeta(
                        'params.'.$paramMeta->name,
                        $paramMeta->type, $paramDoc,
                        ContainerFactory::create($paramMeta->type)
                    )
                );
            }else{
                $paramMeta->source = $params[0];
            }
        }
    }
}