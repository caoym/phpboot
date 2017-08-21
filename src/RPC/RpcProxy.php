<?php
namespace PhpBoot\RPC;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PhpBoot\Application;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\ControllerContainerBuilder;
use PhpBoot\Controller\ExceptionRenderer;
use PhpBoot\Controller\Route;
use PhpBoot\Utils\ArrayAdaptor;
use PhpBoot\Utils\ArrayHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RpcProxy
{
    /**
     * PRC 代理
     *
     * 通过__call实现代理时, 无法传递引用参数, 默认处理方式是抛出异常。如果想忽略此参数, 可以把ignoreReferenceParam设置为 true。
     *
     * 如果需要使用引用参数, 可以继承RpcProxy, 并重对应方法。
     *
     * TODO 支持鉴权
     *
     * RpcProxy constructor.
     * @param Application $app
     * @param ControllerContainerBuilder $builder
     * @param Client $http
     * @param string $interface
     * @param string $prefix
     */
    public function __construct(Application $app,
                                ControllerContainerBuilder $builder,
                                Client $http,
                                $interface,
                                $prefix='/')
    {
        $this->container = $builder->build($interface);
        $this->http = $http;
        $this->uriPrefix = $prefix;
        $this->app = $app;
    }

    public function __call($method, $args)
    {
        $route = $this->container->getRoute($method)
            or \PhpBoot\abort($this->container->getClassName()." $method is not a valid http interface");

        $request = $this->createRequest($method, $route, $args);

        if(MultiRpc::isRunning()){
            $op = $this->http->sendAsync($request);
            $res = MultiRpc::wait($op);
            return $this->mapResponse($method, $route, $res, $args);
        }else{
            $res = $this->http->send($request);
            return $this->mapResponse($method, $route, $res, $args);
        }
    }

    /**
     * @param $actionName
     * @param Route $route
     * @param array $args
     * @return RequestInterface
     */
    public function createRequest($actionName, Route $route, array $args)
    {
        $params = $route->getRequestHandler()->getParamMetas();
        //TODO 支持 query、content、path以外的其他参数, 如cookie,path等
        $request = [];
        foreach ($params as $pos=>$param){
            if(!array_key_exists($pos, $args) && $param->isOptional){
                $args[$pos] = $param->default;
            }
            array_key_exists($pos, $args) or \PhpBoot\abort(
                $this->container->getClassName()." $actionName missing param {$param->name}");

            if(!$param->isPassedByReference){
                ArrayHelper::set($request, $param->source, $args[$pos]);
            }
        }

        if(isset($request['request'])){
            $request = $request['request'];
        }
        $uri = $route->getUri();
        foreach($route->getPathParams() as $path){
            if(isset($request[$path])){
                $uri = str_replace('{'.$path.'}', urlencode($request[$path]) , $uri);
                unset($request[$path]);
            }
        }
        $httpMethod = $route->getMethod();

        $query = [];
        $body = null;
        $headers = [];

        if(isset($request['query'])){
            $query += $request['query'];
        }
        unset($request['query']);

        if(isset($request['headers'])){
            $headers += $request['headers'];
        }
        unset($request['headers']);

        if(isset($request['cookies'])){
            $cookies = [];
            foreach ($request['cookies'] as $k=>$v){
                $cookies[] = "$k=$v";
            }
            $headers['Cookie'] = implode('; ', $cookies);
        }
        unset($request['cookies']);

        if(isset($request['request'])){
            if($body === null){
                $body = [];
            }
            $body += $request['request'];
        }
        unset($request['request']);

        if(isset($request['files'])){
            \PhpBoot\abort(new \UnexpectedValueException("sending request with files is not support"));
        }
        if(in_array($httpMethod, ['GET', 'OPTION'])){
            foreach ($request as $k => $v){
                if(!in_array($k, ['query', 'request', 'files', 'cookies', 'headers'])){
                    $query[$k] = $v;
                }
            }
        }else{
            foreach ($request as $k => $v){
                if(!in_array($k, ['query', 'request', 'files', 'cookies', 'headers'])){
                    if($body === null){
                        $body = [];
                    }
                    $body[$k] = $v;
                }
            }
        }
        if($body !== null){
            $body = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        $uri = $this->uriPrefix.ltrim($uri, '/').'?'.http_build_query($query);
        return new \GuzzleHttp\Psr7\Request(
            $httpMethod,
            $uri,
            $headers,
            $body
        );
    }


    public function mapResponse($actionName, Route $route, ResponseInterface $response, $requestArg=[])
    {
        $response = \Symfony\Component\HttpFoundation\Response::create(
            (string)$response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
            );
        $namedArgs = [];

        foreach ($route->getRequestHandler()->getParamMetas() as $pos=>$param){

            if($param->isPassedByReference){
                $namedArgs[$param->name] = &$requestArg[$pos];
            }
        }


        $content = json_decode((string)$response->getContent(), true);

        //TODO 远端接口没有抛出异常,但设置了 status( status 不是200)时如何处理
        $handler = $route->getResponseHandler();

        if($response->getStatusCode() >= 200 && $response->getStatusCode() <300){


            $returns = $handler->getMappings();
            $buffer = [];
            ArrayHelper::set($buffer, 'response.content', $content);
            ArrayHelper::set($buffer, 'response.headers', new ArrayAdaptor($response->headers));
            ArrayHelper::set($buffer, 'response.cookies', $response->headers->getCookies());

            //TODO 支持 cookie
            //ArrayHelper::set($response, 'response.cookies');


            $mapping = [
                'params'=>$namedArgs
            ];

            foreach ($returns as $map=>$return){

                $data = \JmesPath\search($map, $buffer);
                if(!$return->container){
                    continue;
                }
                $data = $return->container->make($data, false);
                ArrayHelper::set($mapping, $return->source, $data);
            }

        }else{

            //TODO 如果多个 异常对应同一个 statusCode 怎么处理
            $exceptions = $route->getExceptionHandler()->getExceptions();

            $errName = null;
            foreach ($exceptions as $err){

                $renderer = $this->app->get(ExceptionRenderer::class);
                $exec = $renderer->render(
                    $this->app->make($err, ['message'=>(string)$response->getContent()])
                );

                if( $exec->getStatusCode() == $response->getStatusCode()){
                    throw $exec;
                }
            }
            throw new \RuntimeException((string)$response->getContent());

        };

        if(isset($mapping['return'])){
            return $mapping['return'];
        };
    }
    /**
     * @var ControllerContainer
     */
    protected $container;

    /**
     * @var ClientInterface
     */
    protected $http;

    /**
     * @var string
     */
    protected $uriPrefix;

    /**
     * @var Application
     */
    protected $app;
}