<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class BodyParameterObject extends ParameterObject
{
    /**
     * Required. The schema defining the type used for the body parameter.
     * @var PrimitiveSchemaObject|SimpleModelSchemaObject|RefSchemaObject|ArraySchemaObject
     */
    public $schema;
}