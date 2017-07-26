<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class ResponseObject
{
    /**
     * 	Required. A short description of the response. GFM syntax can be used for rich text
     * representation.
     * @var string
     */
    public $description='';
    /**
     * A definition of the response structure. It can be a primitive, an array or an object. If this field
     * does not exist, it means no content is returned as part of the response. As an extension to the Schema
     * Object, its root type value may also be "file". This SHOULD be accompanied by a relevant produces
     * mime-type.
     * @var RefSchemaObject|ArraySchemaObject|SimpleModelSchemaObject|PrimitiveSchemaObject
     */
    public $schema;
    /**
     * Headers Object	A list of headers that are sent with the response.
     *
     * @var HeaderObject[]
     */
    public $headers;

    /**
     * An example of the response message.
     * {
     *   "application/json": {
     *   "name": "Puma",
     *   "type": "Dog",
     *   "color": "Black",
     *   "gender": "Female",
     *   "breed": "Mixed"
     *   }
     }
     * @var array
     */
    public $examples;
}