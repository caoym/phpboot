<?php

namespace PhpBoot\Annotation\Entity\Annotations;


use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;

class ValidateAnnotationHandler extends EntityAnnotationHandler
{
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 3);
        if($params->count()){

            $target = $ann->parent->name;
            $property = $this->container->getProperty($target);
            $property or fail($this->container->getClassName()." property $target not exist ");
            if($params->count()>1){
                $expr = [$params->getParam(0), $params->getParam(1)];
            }else{
                $expr = $params->getParam(0);
            }
            $property->validation = $expr;
        }else{
            fail(new AnnotationSyntaxException(
                "The annotation \"@{$ann->name} {$ann->description}\" of {$this->container->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }
    }
}