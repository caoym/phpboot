<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class LicenseObject
{
    /**
     * Required. The license name used for the API.
     * @var string
     */
    public $name;

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     * @var string
     */
    public $url;

}