<?php

namespace PhpBoot\Annotation\Entity\Annotations;

use PhpBoot\Annotation\Entity\EntityAnnotationHandler;
use PhpBoot\Utils\AnnotationParams;
use PhpBoot\Utils\TypeHint;

class VarAnnotationHandler extends EntityAnnotationHandler
{
    public function handle($ann)
    {
        $params = new AnnotationParams($ann->description, 2);
        if($params->count()){
            $type = $params[0];
            //TODO 校验type类型
            $target = $ann->parent->name;
            $property = $this->builder->getProperty($target);
            $property or fail($this->builder->getClassName()." property $target not exist ");
            if($type){
                // TODO 判断$type是否匹配
                $property->type = TypeHint::normalize($type, $this->builder->getClassName());
            }
        }

    }
}