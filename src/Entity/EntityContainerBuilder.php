<?php

namespace PhpBoot\Entity;

use DI\FactoryInterface;
use PhpBoot\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Entity\Annotations\ValidateAnnotationHandler;
use PhpBoot\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\Annotation\Names;

class EntityContainerBuilder extends ContainerBuilder
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [ValidateAnnotationHandler::class, "properties.*.children[?name=='".Names::VALIDATE."'][]"],
    ];

    /**
     * EntityContainerBuilder constructor.
     * @param FactoryInterface $factory
     * @param array $annotations
     */
    public function __construct(FactoryInterface $factory, array $annotations = self::DEFAULT_ANNOTATIONS)
    {
        $this->factory = $factory;
        parent::__construct($annotations);
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return EntityContainer
     */
    public function build($className)
    {
        return parent::build($className);
    }

    /**
     * @param $className
     * @return EntityContainer
     */
    public function buildWithoutCache($className)
    {
        return parent::buildWithoutCache($className);
    }

    /**
     * @param string $className
     * @return object
     */
    protected function createContainer($className)
    {
        return $this->factory->make(EntityContainer::class, ['className'=>$className]);
    }

    protected function getHandler($handlerName, $container)
    {
        return $this->factory->make($handlerName, ['builder'=>$this, 'container'=>$container]);
    }

    /**
     * @var FactoryInterface
     */
    private $factory;
}