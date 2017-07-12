<?php
namespace PhpBoot\Controller;

use Symfony\Component\HttpFoundation\Request;

class Route
{
    public function __construct(
        $method='',
        $uri='',
        RequestHandler $requestHandler=null,
        ResponseHandler $responseHandler=null,
        $summary = '',
        $description = '')
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->requestHandler = $requestHandler;
        $this->responseHandler = $responseHandler;
        $this->summary = $summary;
        $this->description = $description;
    }

    /**
     * @param callable $function
     * @param Request $request
     */
    public function invoke(callable $function, Request $request)
    {
        $this->requestHandler or fail('undefined requestHandler');
        $this->responseHandler or fail('undefined responseHandler');
        $params = [];
        $this->requestHandler->handle($request, $params);
        $res = call_user_func_array($function, $params);
        return $this->responseHandler->handle($res);
    }
    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
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
     * @return RequestHandler
     */
    public function getRequestHandler()
    {
        return $this->requestHandler;
    }

    /**
     * @param RequestHandler $requestHandler
     */
    public function setRequestHandler($requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @return ResponseHandler
     */
    public function getResponseHandler()
    {
        return $this->responseHandler;
    }

    /**
     * @param ResponseHandler $responseHandler
     */
    public function setResponseHandler($responseHandler)
    {
        $this->responseHandler = $responseHandler;
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
     * @var string
     */
    private $summary = '';
    /**
     * @var string
     */
    private $description='';

}