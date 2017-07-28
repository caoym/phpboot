<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class ExternalDocumentationObject
{
    /**
     * A short description of the target documentation. GFM syntax can be used for rich text representation.
     * @var string
     */
    public $description;
    /**
     * Required. The URL for the target documentation. Value MUST be in the format of a URL
     * @var string
     */
    public $url;
}