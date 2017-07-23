<?php

namespace PhpBoot\Entity\Annotations;

use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationTag;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Exceptions\AnnotationSyntaxException;
use PhpBoot\Utils\AnnotationParams;

class ValidateAnnotationHandler
{
    /**
     * @param EntityContainer $container
     * @param AnnotationBlock|AnnotationTag $ann
     * @return void
     */
    public function __invoke(EntityContainer $container, $ann)
    {
        $params = new AnnotationParams($ann->description, 3);
        if($params->count()){

            $target = $ann->parent->name;
            $property = $container->getProperty($target);
            $property or fail($container->getClassName()." property $target not exist ");
            if($params->count()>1){
                $expr = [$params->getParam(0), $params->getParam(1)];
            }else{
                $expr = $params->getParam(0);
            }
            $property->validation = $expr;
        }else{
            fail(new AnnotationSyntaxException(
                "The annotation \"@{$ann->name} {$ann->description}\" of {$container->getClassName()}::{$ann->parent->name} require 1 param, 0 given"
            ));
        }
    }
}