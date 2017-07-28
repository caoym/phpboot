<?php

namespace PhpBoot\Docgen\Swagger\Schemas;

/**
 * Class RefSchemaObject
 * @package PhpBoot\Docgen\Swagger
 * @property string $$ref
 */
class RefSchemaObject extends SchemaObject
{
    public function __construct($ref)
    {
        $this->{'$ref'} = $ref;
    }
    public function getRef()
    {
        return $this->{'$ref'};
    }
    public function setRef($ref)
    {
        $this->{'$ref'} = $ref;
    }
}