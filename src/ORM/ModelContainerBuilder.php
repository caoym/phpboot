<?php

namespace PhpBoot\ORM;


use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\Annotation\Names;
use PhpBoot\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Entity\Annotations\ValidateAnnotationHandler;
use PhpBoot\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\ORM\Annotations\PKAnnotationHandler;
use PhpBoot\ORM\Annotations\TableAnnotationHandler;


class ModelContainerBuilder extends ContainerBuilder
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PKAnnotationHandler::class, "class.children[?name=='".Names::PK."']"],
        [TableAnnotationHandler::class, "class.children[?name=='".Names::TABLE."']"],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [ValidateAnnotationHandler::class, "properties.*.children[?name=='".Names::VALIDATE."'][]"],
    ];

    public function __construct()
    {
        parent::__construct(self::DEFAULT_ANNOTATIONS);
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return ModelContainer
     */
    public function build($className)
    {
        return parent::build($className);
    }

    /**
     * @param $className
     * @return ModelContainer
     */
    public function buildWithoutCache($className)
    {
        return parent::buildWithoutCache($className);
    }

    /**
     * @param string $className
     * @return ModelContainer
     */
    protected function createContainer($className)
    {
        return new ModelContainer($className);
    }
}