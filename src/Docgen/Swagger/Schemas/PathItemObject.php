<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class PathItemObject extends RefSchemaObject
{
    /**
     * @var OperationObject
     */
    public $put;
    /**
     * @var OperationObject
     */
    public $post;
    /**
     * @var OperationObject
     */
    public $delete;
    /**
     * @var OperationObject
     */
    public $options;
    /**
     * @var OperationObject
     */
    public $head;
    /**
     * @var OperationObject
     */
    public $patch;
    /**
     *
     * A list of parameters that are applicable for all the operations described under this path. These
     * parameters can be overridden at the operation level, but cannot be removed there. The list MUST NOT
     * include duplicated parameters. A unique parameter is defined by a combination of a name and
     * location. The list can use the Reference Object to link to parameters that are defined at the
     * Swagger Object's parameters. There can be one "body" parameter at most.
     * @var ParameterObject[]|OtherParameterObject[]|RefSchemaObject[]
     */
    public $parameters;
}