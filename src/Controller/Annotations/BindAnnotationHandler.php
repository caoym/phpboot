<?php

namespace PhpBoot\Controller\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Metas\ReturnMeta;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;

class BindAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @param EntityContainerBuilder $entityBuilder
     */
    public function __invoke(ControllerContainer $container, $ann, EntityContainerBuilder $entityBuilder)
    {
        if(!$ann->parent || !$ann->parent->parent){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()} should be used with parent param/return");
            return;
        }
        $target = $ann->parent->parent->name;
        $route = $container->getRoute($target);
        if(!$route){
            Logger::debug("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target should be used with parent param/return");
            return ;
        }

        $params = new AnnotationParams($ann->description, 2);

        $params->count()>0 or \PhpBoot\abort(new AnnotationSyntaxException("The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::$target require 1 param, {$params->count()} given"));

        $handler = $route->getResponseHandler();

        if ($ann->parent->name == 'return'){
            list($target, $return) = $handler->getMappingBySource('return');
            if($return){
                $handler->eraseMapping($target);
                $handler->setMapping($params[0], $return);
            }

        }elseif($ann->parent->name == 'param'){
            list($paramType, $paramName, $paramDoc) = ParamAnnotationHandler::getParamInfo($ann->parent->description);

            $paramMeta = $route->getRequestHandler()->getParamMeta($paramName);
            if($paramMeta->isPassedByReference){
                list($target, $ori) = $handler->getMappingBySource('params.'.$paramName);
                if($ori){
                    $handler->eraseMapping($target);
                }
                //输出绑定
                $handler->setMapping(
                    $params[0],
                    new ReturnMeta(
                        'params.'.$paramMeta->name,
                        $paramMeta->type, $paramDoc,
                        ContainerFactory::create($entityBuilder, $paramMeta->type)
                    )
                );
            }else{
                $paramMeta->source = $params[0];
            }
        }
    }
}