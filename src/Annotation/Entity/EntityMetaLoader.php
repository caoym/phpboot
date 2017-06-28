<?php

namespace PhpBoot\Annotation\Entity;


use PhpBoot\Annotation\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Annotation\MetaLoader;
use PhpBoot\Entity\EntityBuilder;
use PhpBoot\Metas\PropertyMeta;

class EntityMetaLoader extends MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [PropertyAnnotationHandler::class, 'properties'],
        [PropertyMeta::class, 'properties[*].var'],
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
        parent::loadFromClassWithoutCache($className);
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