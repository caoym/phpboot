<?php

namespace PhpBoot\DI;

use DI\Definition\ObjectDefinition;
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\DI\Annotations\InjectAnnotationHandler;
use PhpBoot\DI\Annotations\VarAnnotationHandler;

class DIMetaLoader extends ContainerBuilder
{
    static $DEFAULT_ANNOTATIONS=[
        [VarAnnotationHandler::class, "properties.*.children[?name=='var'][]"],
        [InjectAnnotationHandler::class, "properties.*.children[?name=='".PHPBOOT_ANNOTATION_INJECT."'][]"]
    ];

    /**

     * @param array $annotations
     */
    public function __construct(array $annotations = null)
    {
        if($annotations){
            parent::__construct($annotations);
        }else{
            parent::__construct(self::$DEFAULT_ANNOTATIONS);
        }
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