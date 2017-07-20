<?php

namespace PhpBoot\Controller;

use DI\FactoryInterface;
use PhpBoot\Controller\Annotations\BindAnnotationHandler;
use PhpBoot\Controller\Annotations\ClassAnnotationHandler;
use PhpBoot\Controller\Annotations\HookAnnotationHandler;
use PhpBoot\controller\Annotations\ParamAnnotationHandler;
use PhpBoot\Controller\Annotations\PathAnnotationHandler;
use PhpBoot\Controller\Annotations\ReturnAnnotationHandler;
use PhpBoot\Controller\Annotations\RouteAnnotationHandler;
use PhpBoot\Controller\Annotations\ThrowsAnnotationHandler;
use PhpBoot\Controller\Annotations\ValidateAnnotationHandler;
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\Annotation\Names;

class ControllerContainerBuilder extends ContainerBuilder
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
     * ControllerContainerBuilder constructor.
     * @param FactoryInterface $factory
     * @param array $annotations
     */
    public function __construct(FactoryInterface $factory, array $annotations = self::DEFAULT_ANNOTATIONS)
    {
        parent::__construct($annotations);
        $this->factory = $factory;
    }
    /**
     * load from class with local cache
     * @param string $className
     * @return ControllerContainer
     */
    public function build($className)
    {
        return parent::build($className);
    }

    /**
     * @param $className
     * @return ControllerContainer
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
        return $this->factory->make(ControllerContainer::class, ['className'=>$className]);
    }

    protected function getHandler($handlerName, $container)
    {
        return $this->factory->make($handlerName, ['container'=>$container]);
    }


    /**
     * @var FactoryInterface
     */
    private $factory;
}