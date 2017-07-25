<?php

namespace PhpBoot\DI;


use DI\Definition\ObjectDefinition;
use PhpBoot\Metas\PropertyMeta;

class ObjectDefinitionContext
{
    /**
     * @var PropertyMeta[]
     */
    public $vars = [];
    /**
     * @var ObjectDefinition
     */
    public $definition;
}