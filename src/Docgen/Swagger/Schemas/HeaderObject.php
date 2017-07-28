<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class HeaderObject
{
    /**
     * A brief description of the parameter. This could contain examples of use. GFM syntax can be used for rich text representation.
     * @var string
     */
    public $description;

    /**
     * Required. The type of the object. The value MUST be one of "string", "number", "integer", "boolean",
     * or "array".
     * @var string
     */
    public $type;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     * @var string
     */
    public $format;

    /**
     * Items Object	Required if type is "array". Describes the type of items in the array.
     * @var PrimitiveSchemaObject|ArraySchemaObject|RefSchemaObject|SimpleModelSchemaObject
     */
    public $items;
    /**
     * Determines the format of the array if type array is used. Possible values are: csv - comma separated
     * values foo,bar.
     * ssv - space separated values foo bar.
     * tsv - tab separated values foo\tbar.
     * pipes - pipe separated values foo|bar.
     * multi - corresponds to multiple parameter instances instead of multiple values for a single instance
     * foo=bar&foo=baz. This is valid only for parameters in "query" or "formData".
     * Default value is csv.
     * @var string
     */
    public $collectionFormat;

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