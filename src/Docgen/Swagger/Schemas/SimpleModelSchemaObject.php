<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class SimpleModelSchemaObject extends SchemaObject
{
    public $type = "object";
    /**
     * @var string[]
     */
    public $required;

    /**
     * @var PrimitiveSchemaObject[]|RefSchemaObject[]|ArraySchemaObject[]|SimpleModelSchemaObject[]
     */
    public $properties = [];

}