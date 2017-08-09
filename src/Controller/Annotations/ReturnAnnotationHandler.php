<?php

namespace PhpBoot\Controller\Annotations;


use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\Logger;
use PhpBoot\Utils\TypeHint;

class ReturnAnnotationHandler
{
    /**
     * @param ControllerContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @param EntityContainerBuilder $entityBuilder
     */
    public function __invoke(ControllerContainer $container, $ann, EntityContainerBuilder $entityBuilder)
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
        $type = $doc = null;
        if(count($params)>0){
            $type = TypeHint::normalize($params[0], $container->getClassName());
        }
        $doc = $params->getRawParam(1, '');

        list($_, $meta) = $route
            ->getResponseHandler()
            ->getMappingBySource('return');
        if($meta){
            $meta->description = $doc;
            $meta->type = $type;
            $meta->container = $type == 'void'?null:ContainerFactory::create($entityBuilder, $type);
        }
    }
}