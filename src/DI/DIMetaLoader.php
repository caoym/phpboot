<?php

namespace PhpBoot\DI;


use DI\Definition\ObjectDefinition;
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\Annotation\Names;
use PhpBoot\DI\Annotations\InjectAnnotationHandler;
use PhpBoot\DI\Annotations\VarAnnotationHandler;

class DIMetaLoader extends ContainerBuilder
{
    const DEFAULT_ANNOTATIONS=[
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [InjectAnnotationHandler::class, "properties.*.children[?name=='".Names::INJECT."'][]"]
    ];

    /**

     * @param array $annotations
     */
    public function __construct(array $annotations = self::DEFAULT_ANNOTATIONS)
    {
        parent::__construct($annotations);
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