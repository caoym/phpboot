<?php

namespace PhpBoot\Annotation\Entity;


use PhpBoot\Annotation\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Annotation\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Annotation\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\Annotation\MetaLoader;
use PhpBoot\Entity\EntityBuilder;
use PhpBoot\Metas\PropertyMeta;

class EntityMetaLoader extends MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
    ];

    /**
     * EntityMetaLoader constructor.
     * @param array $annotations
     */
    public function __construct(array $annotations = self::DEFAULT_ANNOTATIONS)
    {
        parent::__construct($annotations);
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return EntityBuilder|false
     */
    public function loadFromClass($className)
    {
        return parent::loadFromClass($className);
    }

    /**
     * @param $className
     * @return EntityBuilder
     */
    public function loadFromClassWithoutCache($className)
    {
        return parent::loadFromClassWithoutCache($className);
    }

    /**
     * @param string $className
     * @return object
     */
    protected function createBuilder($className)
    {
        return new EntityBuilder($className);
    }
}