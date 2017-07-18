<?php
namespace PhpBoot\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Route
{
    public function __construct(
        $method='',
        $uri='',
        RequestHandler $requestHandler=null,
        ResponseHandler $responseHandler=null,
        ExceptionHandler $exceptionHandler=null,
        $hooks=[],
        $summary = '',
        $description = '')
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->requestHandler = $requestHandler;
        $this->responseHandler = $responseHandler;
        $this->exceptionHandler = $exceptionHandler;
        $this->hooks = $hooks;
        $this->summary = $summary;
        $this->description = $description;
    }

    /**
     * @param callable $function
     * @param Request $request
     * @return Response
     */
    public function invoke(callable $function, Request $request)
    {
        $this->requestHandler or fail('undefined requestHandler');
        $this->responseHandler or fail('undefined responseHandler');
        $this->exceptionHandler or fail('undefined exceptionHandler');

        $res = $this->exceptionHandler->handler(function()use($request, $function){
            $next = function($request)use($function){
                $params = [];
                $this->requestHandler->handle($request, $params);
                $res = call_user_func_array($function, $params);
                return $this->responseHandler->handle($res, $params);
            };
            foreach (array_reverse($this->hooks) as $hookName){
                $next = function($request)use($hookName, $next){
                    $hook = new $hookName();
                    /**@var $hook HookInterface*/
                    return $hook->handle($request, $next);
                };
            }
            return $next($request);
        });
        return $res;
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
     * @return ExceptionHandler
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * @param ExceptionHandler $exceptionHandler
     */
    public function setExceptionHandler($exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @return string[]
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * @param string[] $hooks
     */
    public function setHooks($hooks)
    {
        $this->hooks = $hooks;
    }

    public function addHook($className)
    {
        $this->hooks[] = $className;
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
     * @var ExceptionHandler
     */
    private $exceptionHandler;

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

    /**
     * hook class names
     * @var string[]
     */
    private $hooks=[];

}