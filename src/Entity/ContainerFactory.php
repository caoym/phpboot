<?php

namespace PhpBoot\Entity;


use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Utils\TypeHint;

class ContainerFactory
{
    static public function create($type)
    {
        //TODO 支持|分隔的多类型

        $getter = function($type){
            if(!$type || $type == 'mixed'){
                return new MixedTypeContainer();
            }elseif (TypeHint::isScalarType($type)){
                return new ScalarTypeContainer($type);
            }else{
                $loader = new EntityMetaLoader();
                return $loader->loadFromClass($type);
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