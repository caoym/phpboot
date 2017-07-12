<?php

namespace PhpBoot\Annotation\Controller;


use PhpBoot\Annotation\Controller\Annotations\ClassAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\RouteAnnotationHandler;
use PhpBoot\Annotation\MetaLoader;
use PhpBoot\Controller\ControllerBuilder;

class ControllerMetaLoader extends MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [RouteAnnotationHandler::class, "methods.*.children[?name=='route'][]"],
    ];

    /**
     * ControllerMetaLoader constructor.
     * @param array $annotations
     */
    public function __construct(array $annotations = self::DEFAULT_ANNOTATIONS)
    {
        parent::__construct($annotations);
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return ControllerBuilder
     */
    public function loadFromClass($className)
    {
        return parent::loadFromClass($className);
    }

    /**
     * @param $className
     * @return ControllerBuilder
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
        return new ControllerBuilder($className);
    }
}