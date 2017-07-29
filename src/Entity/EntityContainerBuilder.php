<?php

namespace PhpBoot\Entity;

use DI\FactoryInterface;
use DI\InvokerInterface as DIInvokerInterface;
use PhpBoot\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Entity\Annotations\ValidateAnnotationHandler;
use PhpBoot\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\Annotation\ContainerBuilder;

class EntityContainerBuilder extends ContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [ValidateAnnotationHandler::class, "properties.*.children[?name=='v'][]"],
    ];

    /**
     * ControllerContainerBuilder constructor.
     * @param FactoryInterface $factory
     * @param DIInvokerInterface $diInvoker
     *
     * @param array $annotations
     */
    public function __construct(FactoryInterface $factory, DIInvokerInterface $diInvoker, array $annotations = null)
    {
        if($annotations){
            parent::__construct($annotations);
        }else{
            parent::__construct(self::$DEFAULT_ANNOTATIONS);
        }
        $this->factory = $factory;
        $this->diInvoker = $diInvoker;
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
     * @return EntityContainer
     */
    protected function createContainer($className)
    {
        return $this->factory->make(EntityContainer::class, ['className'=>$className]);
    }

    protected function handleAnnotation($handlerName, $container, $ann)
    {
        $handler = $this->factory->make($handlerName);
        return $this->diInvoker->call($handler, [$container, $ann]);
    }


    /**
     * @var FactoryInterface
     */
    protected $factory;
    /**
     * @var DIInvokerInterface
     */
    protected $diInvoker;
}