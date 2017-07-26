<?php

namespace PhpBoot\Docgen\Swagger\Schemas;

/**
 * Allows adding meta data to a single tag that is used by the @see OperationObject . It is not mandatory to have
 * a Tag Object per tag used there.
 */
class TagObject
{
    /**
     * Required. The name of the tag.
     * @var string
     */
    public $name;
    /**
     * A short description for the tag. GFM syntax can be used for rich text representation.
     * @var string
     */
    public $description;

    /**
     * @var ExternalDocumentationObject
     */
    public $externalDocs;
}