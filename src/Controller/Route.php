<?php
namespace PhpBoot\Controller;

use PhpBoot\Application;
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
     * @param Application $app
     * @param callable $function
     * @param Request $request
     * @return Response
     */
    public function invoke(Application $app, callable $function, Request $request)
    {
        $this->requestHandler or \PhpBoot\abort('undefined requestHandler');
        $this->responseHandler or \PhpBoot\abort('undefined responseHandler');
        $this->exceptionHandler or \PhpBoot\abort('undefined exceptionHandler');

        $res = $this->exceptionHandler->handler(
            $app,
            function()use($app, $request, $function){

                $next = function($request)use($app, $function){
                    $params = [];
                    $reference = [];
                    $this->requestHandler->handle($app, $request, $params, $reference);
                    $res = call_user_func_array($function, $params);
                    return $this->responseHandler->handle($app, $res, $reference);
                };
                foreach (array_reverse($this->hooks) as $hookName){
                    $next = function($request)use($app, $hookName, $next){
                        $hook = $app->get($hookName);
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
     * @return string[]
     */
    public function getPathParams()
    {
        return $this->pathParams;
    }

    /**
     * @param string[] $pathParams
     */
    public function setPathParams($pathParams)
    {
        $this->pathParams = $pathParams;
    }
    /**
     * @param string $pathParam
     */
    public function addPathParam($pathParam)
    {
        $this->pathParams[] = $pathParam;
        array_unique($this->pathParams);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPathParam($name)
    {
        return in_array($name, $this->pathParams);
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

    /**
     * @var string[]
     */
    private $pathParams =[];

}