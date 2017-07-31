<?php

namespace PhpBoot\DI;

use DI\Definition\ObjectDefinition;
use Doctrine\Common\Cache\Cache;
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\DI\Annotations\InjectAnnotationHandler;
use PhpBoot\DI\Annotations\VarAnnotationHandler;

class DIMetaLoader extends ContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [InjectAnnotationHandler::class, "properties.*.children[?name=='inject'][]"]
    ];


    public function __construct(Cache $cache)
    {
        parent::__construct(self::$DEFAULT_ANNOTATIONS, $cache);
    }

    /**
     * @param string $className
     * @return object
     */
    protected function createContainer($className)
    {
        $res = new ObjectDefinitionContext();
        $res->definition = new ObjectDefinition($className);
        return $res;
    }

    protected function getHandler($handlerName, $container)
    {
        return new $handlerName($container);
    }
}