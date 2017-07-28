<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class ArraySchemaObject extends SchemaObject
{
    /**
     * @var string
     */
    public $type = 'array';
    /**
     * @var PrimitiveSchemaObject|ArraySchemaObject|RefSchemaObject|SimpleModelSchemaObject
     */
    public $items;
}