<?php

namespace PhpBoot\Entity;

use PhpBoot\Utils\TypeHint;

class ContainerFactory
{
    static public function create(EntityContainerBuilder $builder, $type)
    {
        //TODO 支持|分隔的多类型

        $getter = function($type)use($builder){
            if(!$type || $type == 'mixed'){
                return new MixedTypeContainer();
            }elseif (TypeHint::isScalarType($type)){
                return new ScalarTypeContainer($type);
            }else{
                return $builder->build($type);
            }
        };
        if(TypeHint::isArray($type)){
            $container = ArrayContainer::create($type, $getter);
        }else{
            $container = $getter($type);
        }
        return $container;
    }
}