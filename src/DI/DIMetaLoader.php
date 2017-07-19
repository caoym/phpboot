<?php

namespace PhpBoot\DI;


use DI\Definition\ObjectDefinition;
use PhpBoot\Annotation\MetaLoader;
use PhpBoot\Annotation\Names;
use PhpBoot\DI\Annotations\InjectAnnotationHandler;
use PhpBoot\DI\Annotations\VarAnnotationHandler;

class DIMetaLoader extends MetaLoader
{
    const DEFAULT_ANNOTATIONS=[
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [InjectAnnotationHandler::class, "properties.*.children[?name=='".Names::INJECT."'][]"],
    ];

    /**
     * EntityMetaLoader constructor.
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
}