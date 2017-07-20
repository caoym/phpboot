<?php

namespace PhpBoot\Controller\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\Annotations\ControllerAnnotationHandler;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class ReturnAnnotationHandler extends ControllerAnnotationHandler
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
        $type = $doc = null;
        if(count($params)>0){
            $type = TypeHint::normalize($params[0], $this->container->getClassName());
        }
        $doc = $params->getRawParam(1, '');

        //TODO 支持 @bind
        $meta = $route
            ->getResponseHandler()
            ->getMapping('response.content');
        if($meta){
            $meta->description = $doc;
            $meta->type = $type;
            $meta->container = ContainerFactory::create($type);
        }
    }
}