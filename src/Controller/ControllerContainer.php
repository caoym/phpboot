<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/9/27
 * Time: 下午3:43
 */

namespace Once\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Application;
use Once\Utils\AnnotationsVisitor;
use Once\Utils\Verify;

/**
 * Class ControllerContainer
 * @package once
 *
 * ControllerContainer 和 Annotations 的关系:
 * ControllerContainer提供原始的能力和扩展点, Annotations负责组织这些能力和扩展, 每一个Annotation应该是独立的, 不依赖与其他Annotation
 * TODO * 支持自定义Annotation
 */
class ControllerContainer extends ClassAnnotations
{

    /**
     * ControllerContainer constructor.
     * @param $className
     */
    public function __construct($className)
    {
        $this->className = $className;
        //TODO 缓存
        $refl = new \ReflectionClass($className);
        $docFactory  = AnnotationsVisitor::createDocBlockFactory();
        if($refl->getDocComment()){
            $docblock = $docFactory->create($refl->getDocComment());
            $this->doc = $docblock->getSummary()."\n".$docblock->getDescription();
        }
        $this->fileName = $refl->getFileName();
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
     * 添加路由
     * @param Route $route
     * @param $actionName class method
     * @return void
     */
    public function addRoute($actionName, Route $route){
        !array_has($this->routes, $actionName) or Verify::fail("repeated @route for {$this->className}::$actionName");
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
        if (array_has($this->routes, $actionName)){
            return $this->routes[$actionName];
        }
        return null;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param ActionInvoker $action
     * @return Response
     */
    public function dispatchAction(Application $app, Request $request, ActionInvoker $action){
        $context = new Context($app, $request);
        $action->invoke($this->getControllerInstance($app), $context);
        return $context->getResponse();
    }


    /**
     * 应用路由, 使路由生效
     * @param Application $app
     */
    public function applyRoutes(Application $app){
        $thiz = $this;
        foreach ($this->routes as $route){
            $app->addRoute(
                $route->getMethod(),
                rtrim($this->path, '/').'/'.ltrim($route->getUri(),'/'),
                [
                    'middleware'=>$route->getMiddlewares(),
                    function(Request $request)use($thiz, $app, $route){
                        return $thiz->dispatchAction($app, $request, $route->getActionInvoker());
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

    private $className;

    private $instance;

    /**
     * @var Route[]
     */
    private $routes=[];

    /**
     * @var string
     */
    private $doc = "";

    /**
     * @var string
     */
    private $fileName;
}