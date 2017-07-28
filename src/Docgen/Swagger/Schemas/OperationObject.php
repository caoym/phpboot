<?php

namespace PhpBoot\Docgen\Swagger\Schemas;


class OperationObject
{
    /**
     * A list of tags for API documentation control. Tags can be used for logical grouping of operations by resources or any other qualifier.
     * @var string[]
     */
    public $tags;

    /**
     * A short summary of what the operation does. For maximum readability in the swagger-ui, this field SHOULD be less than 120 characters.
     * @var string
     */
    public $summary;

    /**
     * A verbose explanation of the operation behavior. GFM syntax can be used for rich text representation.
     * @var string
     */
    public $description;

    /**
     * Additional external documentation for this operation.
     * @var ExternalDocumentationObject
     */
    public $externalDocs;
    /**
     * Unique string used to identify the operation. The id MUST be unique among all operations described in the API. Tools and libraries MAY use the operationId to uniquely identify an operation, therefore, it is recommended to follow common programming naming conventions.
     * @var string
     */
    public $operationId;

    /**
     * A list of MIME types the operation can consume. This overrides the consumes definition at the Swagger Object. An empty value MAY be used to clear the global definition. Value MUST be as described under Mime Types.
     * @var string[]
     */
    public $consumes;

    /**
     * A list of MIME types the operation can produce. This overrides the produces definition at the Swagger Object. An empty value MAY be used to clear the global definition. Value MUST be as described under Mime Types.
     * @var string[]
     */
    public $produces;
    /**
     * A list of parameters that are applicable for this operation. If a parameter is already defined at the
     * Path Item, the new definition will override it, but can never remove it. The list MUST NOT include
     * duplicated parameters. A unique parameter is defined by a combination of a name and location. The list
     * can use the Reference Object to link to parameters that are defined at the Swagger Object's parameters.
     * There can be one "body" parameter at most.
     * @var ParameterObject[]|OtherParameterObject[]|RefSchemaObject[]
     */
    public $parameters;
    /**
     * Required. The list of possible responses as they are returned from executing this operation
     * @var ResponseObject
     */
    public $responses;
    /**
     * The transfer protocol for the operation. Values MUST be from the list: "http", "https", "ws", "wss".
     * The value overrides the Swagger Object schemes definition.
     * @var string[]
     */
    public $schemes=['http'];
    /**
     * Declares this operation to be deprecated. Usage of the declared operation should be refrained. Default value is false.
     * @var bool
     */
    public $deprecated=false;
    /**
     * A declaration of which security schemes are applied for this operation. The list of values describes
     * alternative security schemes that can be used (that is, there is a logical OR between the security
     * requirements). This definition overrides any declared top-level security. To remove a top-level
     * security declaration, an empty array can be used.
     * @var SecurityRequirementObject[]
     */
    public $security;

}