<?php

namespace PhpBoot\ORM;

use DI\Container;
use DI\FactoryInterface;
use Doctrine\Common\Cache\Cache;
use PhpBoot\DI\DIContainerBuilder;
use PhpBoot\Entity\Annotations\ClassAnnotationHandler;
use PhpBoot\Entity\Annotations\PropertyAnnotationHandler;
use PhpBoot\Entity\Annotations\VarAnnotationHandler;
use PhpBoot\Entity\EntityContainerBuilder;
use PhpBoot\ORM\Annotations\PKAnnotationHandler;
use PhpBoot\ORM\Annotations\TableAnnotationHandler;
use DI\InvokerInterface as DIInvokerInterface;

class ModelContainerBuilder extends EntityContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [ClassAnnotationHandler::class, 'class'],
        [PKAnnotationHandler::class, "class.children[?name=='pk']"],
        [TableAnnotationHandler::class, "class.children[?name=='table']"],
        [PropertyAnnotationHandler::class, 'properties'],
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        //[ValidateAnnotationHandler::class, "properties.*.children[?name=='".Names::VALIDATE."'][]"],
    ];

    public function __construct(FactoryInterface $factory,
                                DIInvokerInterface $diInvoker,
                                Cache $cache)
    {
        $this->container = DIContainerBuilder::buildDevContainer();
        parent::__construct($factory, $diInvoker, $cache, self::$DEFAULT_ANNOTATIONS);
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
        return $this->factory->make(ModelContainer::class, ['className'=>$className]);
    }
}