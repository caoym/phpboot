<?php
namespace PhpBoot\Docgen\Swagger\Schemas;

/**
 * Class Swagger
 * @package PhpBoot\Docgen
 */
class SwaggerObject
{
    public function __construct()
    {
        $this->info = new InfoObject();
    }
    /**
     * Required.
     * Specifies the Swagger Specification version being used.
     * It can be used by the Swagger UI and other clients to interpret the API listing.
     * The value MUST be "2.0".
     * @var string
     */
    public $swagger = '2.0';
    /**
     * Required.
     * Provides metadata about the API. The metadata can be used by the clients if needed.
     * @var InfoObject
     */
    public $info;

    /**
     * The host (name or ip) serving the API. This MUST be the host only and does not include the scheme nor sub-paths. It MAY include a port. If the host is not included, the host serving the documentation is to be used (including the port). The host does not support path templating.
     * @var string
     */
    public $host = 'localhost';

    /**
     * The base path on which the API is served, which is relative to the host. If it is not included, the API is served directly under the host. The value MUST start with a leading slash (/). The basePath does not support path templating.
     * @var string
     */
    public $basePath = '/';
    /**
     * The transfer protocol of the API. Values MUST be from the list: "http", "https", "ws", "wss". If the schemes is not included, the default scheme to be used is the one used to access the Swagger definition itself.
     * @var string[]
     */
    public $schemes = ['http'];

    /**
     * A list of MIME types the APIs can consume. This is global to all APIs but can be overridden on specific API calls. Value MUST be as described under Mime Types.
     * @var string[]
     */
    public $consumes = ['application/json'];
    /**
     * A list of MIME types the APIs can produce. This is global to all APIs but can be overridden on specific API calls. Value MUST be as described under Mime Types.
     * @var string[]
     */
    public $produces= ['application/json'];

    /**
     * @var PathItemObject[]
     */
    public $paths=[];

    /**
     * An object to hold data types produced and consumed by operations.
     * @var RefSchemaObject[]|SimpleModelSchemaObject[]|PrimitiveSchemaObject[]|ArraySchemaObject[]
     */
    public $definitions=[];
    /**
     * An object to hold parameters that can be used across operations. This property does not define global
     * parameters for all operations.
     * @var ParameterObject[]|OtherParameterObject[]
     */
    public $parameters;
    /**
     * An object to hold responses that can be used across operations. This property does not define global responses for all operations.
     * @var ResponseObject[]
     */
    public $responses;
    /**
     * Security scheme definitions that can be used across the specification.
     * @var SecuritySchemeObject
     */
    public $securityDefinitions;
    /**
     *  A declaration of which security schemes are applied for the API as a whole. The list of values
     * describes alternative security schemes that can be used (that is, there is a logical OR between the
     * security requirements). Individual operations can override this definition.
     * @var SecurityRequirementObject
     */
    public $security;

    /**
     * A list of tags used by the specification with additional metadata. The order of the tags can be used
     * to reflect on their order by the parsing tools. Not all tags that are used by the Operation Object
     * must be declared. The tags that are not declared may be organized randomly or based on the tools'
     * logic. Each tag name in the list MUST be unique.
     * @var TagObject[]
     */
    public $tags;
    /**
     * @var ExternalDocumentationObject
     */
    public $externalDocs;

}