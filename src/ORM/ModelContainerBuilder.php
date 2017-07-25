<?php

namespace PhpBoot\ORM;

use DI\Container;
use PhpBoot\Annotation\Names;
use PhpBoot\DI\DIContainerBuilder;
use PhpBoot\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Entity\Annotations\ValidateAnnotationHandler;
use PhpBoot\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\ORM\Annotations\PKAnnotationHandler;
use PhpBoot\ORM\Annotations\TableAnnotationHandler;


class ModelContainerBuilder extends EntityContainerBuilder
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PKAnnotationHandler::class, "class.children[?name=='".Names::PK."']"],
        [TableAnnotationHandler::class, "class.children[?name=='".Names::TABLE."']"],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        //[ValidateAnnotationHandler::class, "properties.*.children[?name=='".Names::VALIDATE."'][]"],
    ];

    public function __construct()
    {
        $this->container = DIContainerBuilder::buildDevContainer();
        parent::__construct($this->container, $this->container, self::DEFAULT_ANNOTATIONS);
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
     * @var Container
     */
    private $container;
}