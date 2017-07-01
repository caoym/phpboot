<?php

namespace PhpBoot\Annotation\Entity\Annotations;

use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class VarAnnotationHandler extends EntityAnnotationHandler
{
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        if($params->count()){
            $type = $params->getParam(0);
            //TODO 校验type类型
            $target = $ann->parent->name;
            $property = $this->builder->getProperty($target);
            $property or fail($this->builder->getClassName()." property $target not exist ");
            if($type){
                // TODO 判断$type是否匹配
                $property->type = TypeHint::normalize($type, $this->builder->getClassName());
                $class = $property->type;
                while(TypeHint::isArray($class)){
                    $class = TypeHint::getArrayType($class);
                }
                if(!TypeHint::isScalarType($class)){
                    class_exists($class) or fail(new AnnotationSyntaxException(
                        "{$this->builder->getClassName()}::{$ann->parent->name} @{$ann->name} error, class $class not exist"
                    ));
                    $loader = new EntityMetaLoader();
                    $property->builder = $loader->loadFromClass($class);
                }
            }
        }else{
            fail(new AnnotationSyntaxException(
                "The annotation @{$ann->name} of {$this->builder->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }

    }
}