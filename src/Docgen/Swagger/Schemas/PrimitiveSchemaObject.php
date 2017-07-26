<?php

namespace PhpBoot\Docgen\Swagger\Schemas;

/**
 *
 */
class PrimitiveSchemaObject extends SchemaObject
{
    /**
     * integer	integer	int32	signed 32 bits
     * long	integer	int64	signed 64 bits
     * float	number	float
     * double	number	double
     * string	string
     * byte	string	byte	base64 encoded characters
     * binary	string	binary	any sequence of octets
     * boolean	boolean
     * date	string	date	As defined by full-date - RFC3339
     * dateTime	string	date-time	As defined by date-time - RFC3339
     * password	string	password	Used to hint UIs the input needs to be obscured.
     * @var string
     */
    public $type;
    /**
     * the format property is an open string-valued property, and can have any value to support documentation needs. Formats such as "email", "uuid", etc., can be used even though they are not defined by this specification.
     * @var string
     */
    public $format;

    public $multipleOf;
    /**@var int */
    public $maximum;
    /**@var int */
    public $exclusiveMaximum;
    /**@var int */
    public $minimum;
    /**@var int */
    public $exclusiveMinimum;
    /**@var int */
    public $maxLength;
    /**@var int */
    public $minLength;
    /**@var string */
    public $pattern;
    /**@var int */
    public $maxItems;
    /**@var int */
    public $minItems;
    /**@var bool */
    public $uniqueItems;
    /**@var int */
    public $maxProperties;
    /**@var int */
    public $minProperties;
    /**@var bool */
    public $required;
    /**@var string[] */
    public $enum;
    /**@var mixed */
    public $default;

}