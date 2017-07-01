<?php

namespace PhpBoot\Annotation\Entity\Annotations;


use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;

class ValidatorAnnotationHandler extends EntityAnnotationHandler
{
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        if($params->count()){
            $expr = $params->getParam(0);
            //TODO 校验type类型
            $target = $ann->parent->name;
            $property = $this->builder->getProperty($target);
            $property or fail($this->builder->getClassName()." property $target not exist ");
            $property->validation = $expr;
        }else{
            fail(new AnnotationSyntaxException(
                "The annotation @{$ann->name} of {$this->builder->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }

    }
}