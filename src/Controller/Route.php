<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/11/4
 * Time: 下午7:02
 */

namespace Once\Container;


use Once\Container\ActionInvoker;

class Route
{

    /**
     * Route constructor.
     * @param string $method
     * @param string $uri
     * @param string $middlewares
     * @param ActionInvoker $actionInvoker
     */
    public function __construct($method, $uri, $middlewares, ActionInvoker $actionInvoker, $doc)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->middlewares = $middlewares;
        $this->actionInvoker = $actionInvoker;
        $this->doc = $doc;
    }

    /**
     * @return string
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @param string $doc
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;
    }
    /**
     * @return ActionInvoker
     */
    public function getActionInvoker()
    {
        return $this->actionInvoker;
    }

    /**
     * @param \Once\Container\ActionInvoker $actionInvoker
     */
    public function setActionInvoker($actionInvoker)
    {
        $this->actionInvoker = $actionInvoker;
    }
    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param string $middlewares
     */
    public function setMiddlewares($middlewares)
    {
        $this->middlewares = $middlewares;
    }
    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }
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
     * 中间件 多个中间件用|拼接
     * @var string
     */
    private $middlewares;

    /**
     * action invoker
     * @var ActionInvoker
     */
    private $actionInvoker;

    /**
     * 文档
     * @var string
     */
    private $doc = "";
}