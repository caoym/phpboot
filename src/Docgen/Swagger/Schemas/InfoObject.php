<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class InfoObject
{
    /**
     * Required. The title of the application.
     * @var string
     */
    public $title='';
    /**
     * A short description of the application. GFM syntax can be used for rich text representation
     * @var string
     */
    public $description = '';
    /**
     * The Terms of Service for the API.
     * @var string
     */
    public $termsOfService;
    /**
     * The contact information for the exposed API.
     * @var ContactObject
     */
    public $contact;
    /**
     * The license information for the exposed API.
     * @var LicenseObject
     */
    public $license;
    /**
     * 	Required Provides the version of the application API (not to be confused with the specification version)
     * @var string
     */
    public $version='';
}