<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class ContactObject
{
    /**
     * The identifying name of the contact person/organization.
     * @var	string
     */
    public $name;
    /**
     * The URL pointing to the contact information. MUST be in the format of a URL.
     * @var string
     */
    public $url;
    /**
     * The email address of the contact person/organization. MUST be in the format of an email address.
     * @var string
     */
    public $email;
}