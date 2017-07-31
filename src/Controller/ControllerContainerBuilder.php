<?php

namespace PhpBoot\Controller;

use DI\FactoryInterface;
use \DI\InvokerInterface as DIInvokerInterface;
use Doctrine\Common\Cache\Cache;
use PhpBoot\Cache\LocalCacheInterface;
use PhpBoot\Controller\Annotations\BindAnnotationHandler;
use PhpBoot\Controller\Annotations\ClassAnnotationHandler;
use PhpBoot\Controller\Annotations\HookAnnotationHandler;
use PhpBoot\Controller\Annotations\ParamAnnotationHandler;
use PhpBoot\Controller\Annotations\PathAnnotationHandler;
use PhpBoot\Controller\Annotations\ReturnAnnotationHandler;
use PhpBoot\Controller\Annotations\RouteAnnotationHandler;
use PhpBoot\Controller\Annotations\ThrowsAnnotationHandler;
use PhpBoot\Controller\Annotations\ValidateAnnotationHandler;
use PhpBoot\Annotation\ContainerBuilder;

class ControllerContainerBuilder extends ContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PathAnnotationHandler::class, "class.children[?name=='path']"],
        [RouteAnnotationHandler::class, "methods.*.children[?name=='route'][]"],
        [ParamAnnotationHandler::class, "methods.*.children[?name=='param'][]"],
        [ReturnAnnotationHandler::class, "methods.*.children[?name=='return'][]"],
        [BindAnnotationHandler::class, "methods.*.children[].children[?name=='bind'][]"],
        [ThrowsAnnotationHandler::class, "methods.*.children[?name=='throws'][]"],
        [ValidateAnnotationHandler::class, "methods.*.children[].children[?name=='v'][]"],
        [HookAnnotationHandler::class, "methods.*.children[?name=='hook'][]"],
    ];

    /**
     * ControllerContainerBuilder constructor.
     * @param FactoryInterface $factory
     * @param DIInvokerInterface $diInvoker
     * @param Cache $cache
     * @param array $annotations
     */
    public function __construct(FactoryInterface $factory,
                                DIInvokerInterface $diInvoker,
                                Cache $cache,
                                array $annotations = null)
    {
        if($annotations){
            parent::__construct($annotations, $cache);
        }else{
            parent::__construct(self::$DEFAULT_ANNOTATIONS, $cache);
        }

        $this->factory = $factory;
        $this->diInvoker = $diInvoker;
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

    protected function handleAnnotation($handlerName, $container, $ann)
    {
        $handler = $this->factory->make($handlerName);
        return $this->diInvoker->call($handler, [$container, $ann]);
    }


    /**
     * @var FactoryInterface
     */
    private $factory;
    /**
     * @var DIInvokerInterface
     */
    private $diInvoker;
}