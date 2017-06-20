<?php
namespace PhpBoot\Container;


class Route
{
    /**
     * Route constructor.
     * @param string $method
     * @param string $uri
     * @param string[] $middlewares
     * @param ActionInvoker $actionInvoker
     */
    public function __construct( $method, $uri, $middlewares, ActionInvoker $actionInvoker, $doc)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->middlewares = $middlewares;
        $this->actionInvoker = $actionInvoker;
        $this->doc = $doc;
    }

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * http method
     * @var string
     */
    private $method;

    /**
     * uri
     * @var string
     */
    private $uri;

    /**
     * 文档
     * @var string
     */
    private $doc = "";

    /**
     * 中间件
     * @var string[]
     */
    private $middlewares;


}