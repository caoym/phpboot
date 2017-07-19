<?php

namespace PhpBoot\Annotation\Controller;

use PhpBoot\Annotation\Controller\Annotations\BindAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\ClassAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\HookAnnotationHandler;
use PhpBoot\Annotation\controller\Annotations\ParamAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\PathAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\ReturnAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\RouteAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\ThrowsAnnotationHandler;
use PhpBoot\Annotation\Controller\Annotations\ValidateAnnotationHandler;
use PhpBoot\Annotation\MetaLoader;
use PhpBoot\Annotation\Names;
use PhpBoot\Controller\ControllerContainer;

class ControllerMetaLoader extends MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PathAnnotationHandler::class, "class.children[?name=='".Names::PATH."']"],
        [RouteAnnotationHandler::class, "methods.*.children[?name=='".Names::ROUTE."'][]"],
        [ParamAnnotationHandler::class, "methods.*.children[?name=='param'][]"],
        [ReturnAnnotationHandler::class, "methods.*.children[?name=='return'][]"],
        [BindAnnotationHandler::class, "methods.*.children[].children[?name=='".Names::BIND."'][]"],
        [ThrowsAnnotationHandler::class, "methods.*.children[?name=='throws'][]"],
        [ValidateAnnotationHandler::class, "methods.*.children[].children[?name=='".Names::VALIDATE."'][]"],
        [HookAnnotationHandler::class, "methods.*.children[?name=='".Names::HOOK."'][]"],
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
     * @return ControllerContainer
     */
    public function loadFromClass($className)
    {
        return parent::loadFromClass($className);
    }

    /**
     * @param $className
     * @return ControllerContainer
     */
    public function loadFromClassWithoutCache($className)
    {
        return parent::loadFromClassWithoutCache($className);
    }

    /**
     * @param string $className
     * @return object
     */
    protected function createContainer($className)
    {
        return new ControllerContainer($className);
    }
}