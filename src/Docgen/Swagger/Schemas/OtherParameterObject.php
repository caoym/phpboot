<?php

namespace PhpBoot\Docgen\Swagger\Schemas;

/**
 *
 */
class OtherParameterObject extends ParameterObject
{
    /**
     * Required. The type of the parameter. Since the parameter is not located at the request body, it is
     * limited to simple types (that is, not an object). The value MUST be one of "string", "number",
     * "integer", "boolean", "array" or "file". If type is "file", the consumes MUST be either
     * "multipart/form-data","application/x-www-form-urlencoded" or both and the parameter MUST be in
     * "formData".
     * @var string
     */
    public $type;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     * @var string
     */
    public $format;
    /**
     * Sets the ability to pass empty-valued parameters. This is valid only for either query or formData
     * parameters and allows you to send a parameter with a name only or an empty value. Default value is false
     * @var bool
     */
    public $allowEmptyValue;
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