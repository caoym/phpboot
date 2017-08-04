<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\Entity\MixedTypeContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class VarAnnotationHandler
{
    /**
     * @param EntityContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @param EntityContainerBuilder $builder
     * @return void
     */
    public function __invoke(EntityContainer $container, $ann, EntityContainerBuilder $builder)
    {
        $params = new AnnotationParams($ann->description, 3);
        if($params->count()){
            $type = $params->getParam(0);
            //TODO 校验type类型
            $target = $ann->parent->name;
            $property = $container->getProperty($target);
            $property or \PhpBoot\abort($container->getClassName()." property $target not exist ");
            if($type == null || $type == 'mixed'){
                $property->container = new MixedTypeContainer();
            } else{
                // TODO 判断$type是否匹配
                $property->type = TypeHint::normalize($type, $container->getClassName());
                // TODO 防止递归死循环
                $property->container = ContainerFactory::create($builder, $property->type);
            }
        }else{
            \PhpBoot\abort(new AnnotationSyntaxException(
                "The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }

    }
}