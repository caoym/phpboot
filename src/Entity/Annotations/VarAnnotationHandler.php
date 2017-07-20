<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Entity\ContainerFactory;
use PhpBoot\Entity\MixedTypeContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class VarAnnotationHandler extends EntityAnnotationHandler
{
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 3);
        if($params->count()){
            $type = $params->getParam(0);
            //TODO 校验type类型
            $target = $ann->parent->name;
            $property = $this->container->getProperty($target);
            $property or fail($this->container->getClassName()." property $target not exist ");
            if($type == null || $type == 'mixed'){
                $property->container = new MixedTypeContainer();
            } else{
                // TODO 判断$type是否匹配
                $property->type = TypeHint::normalize($type, $this->container->getClassName());

                $property->container = ContainerFactory::create($this->builder, $property->type);
            }
        }else{
            fail(new AnnotationSyntaxException(
                "The annotation \"@{$ann->name} {$ann->description}\" of {$this->container->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }

    }
}