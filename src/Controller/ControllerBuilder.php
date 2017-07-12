<?php

namespace PhpBoot\Controller;
use PhpBoot\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ControllerBuilder
 */
class ControllerBuilder
{
    public function __construct($className)
    {
        $this->className = $className;
    }
    /**
     * 添加路由
     * @param Route $route
     * @param string $actionName class method
     * @return void
     */
    public function addRoute($actionName, Route $route){
        !array_key_exists($actionName, $this->routes) or fail("repeated @route for {$this->className}::$actionName");
        $this->routes[$actionName] = $route;
    }
    /**
     * 获取路由列表
     * @return Route[]
     */
    public function getRoutes(){
        return $this->routes;
    }

    /**
     * 获取路由列表
     * @params Route[] $routes
     */
    public function setRoutes($routes){
        $this->routes = $routes;
    }
    /**
     * 获取指定名称的路由
     * @param $actionName
     * @return Route|null
     */
    public function getRoute($actionName){
        if (array_key_exists($actionName, $this->routes)){
            return $this->routes[$actionName];
        }
        return null;
    }

    static public function dispatch(
        Application $app,
        $className,
        $actionName,
        Route $route,
        Request $request){

        $ctrl = $app->make($className);
        return $route->invoke($ctrl, $request);
    }


    /**
     * 应用路由, 使路由生效
     * @param Application $app
     */
    public function applyRoutes(Application $app){
        foreach ($this->routes as $route){
            $app->addRoute(
                $route->getMethod(),
                rtrim($this->path, '/').'/'.ltrim($route->getUri(),'/'),
                [
                    'middleware'=>$route->getMiddlewares(),
                    function(Request $request)use($app, $route){
                        return $this->dispatchAction($app, $request, $route->getActionInvoker());
                    }
                ]
            );
        }

    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * 获取uri前缀
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->path;
    }

    /**
     * 设置uri前缀
     * @param string $prefix
     */
    public function setPathPrefix($prefix)
    {
        $this->path = $prefix;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
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
    public function getDescription()
    {
        return $this->description;
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
     * @param Application $app
     * @return object return instance of $this->className
     */
    private function getControllerInstance(Application $app){
        if($this->instance){
            return $this->instance;
        }
        //inject dependency
        $this->instance = $app->make($this->className);
        return $this->instance;
    }

    /**
     * @var string
     * the prefix path for all routes of the controller
     */
    private $path;

    /**
     * @var string
     */
    private $className;

    /**
     * @var mixed
     */
    private $instance;

    /**
     * @var Route[]
     */
    private $routes=[];

    /**
     * @var string
     */
    private $description='';
    /**
     * @var string
     */
    private $summary='';

    /**
     * @var string
     */
    private $fileName;
}