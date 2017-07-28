<?php
namespace PhpBoot;

use DI\Container;
use DI\FactoryInterface;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\CacheProvider;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use PhpBoot\Controller\ControllerContainerBuilder;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\ClassModifiedChecker;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\ExceptionRenderer;
use PhpBoot\Controller\Route;
use PhpBoot\DB\DB;
use PhpBoot\DI\DIContainerBuilder;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use PhpBoot\Lock\LocalAutoLock;
use PhpBoot\Utils\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements ContainerInterface, FactoryInterface, \DI\InvokerInterface
{
    use EnableDIAnnotations;

    /**
     * @param string|array
     * .php file
     * ```
     * return
     * [
     *      'DB'=>['user'=>'', 'password'=>''],
     *      'LocalCache'=> \DI\object(ApcCache::class),
     * ];
     * ```
     * or just the array
     * @return self
     */
    static public function createByDefault($conf = [])
    {
        $builder = new DIContainerBuilder();

        $default = [
            'AppName' => 'App',

            'DB.connection' => 'mysql:dbname=default;host=localhost',
            'DB.username' => 'root',
            'DB.password' => 'root',
            'DB.options' => [],

            DB::class => \DI\factory([DB::class, 'connect'])
                ->parameter('dsn', \DI\get('DB.connection'))
                ->parameter('username', \DI\get('DB.username'))
                ->parameter('password', \DI\get('DB.password'))
                ->parameter('options', \DI\get('DB.options')),

            LoggerInterface::class => \DI\object(\Monolog\Logger::class)
                ->constructor(\DI\get('AppName')),

            Request::class => \DI\factory([Request::class, 'createFromGlobals']),
        ];

        $builder->addDefinitions($default);
        $builder->addDefinitions($conf);

        $container = $builder->build();

        Logger::setDefaultLogger($container->get(LoggerInterface::class));

        $app = $container->make(self::class);
        return $app;
    }

    public function __construct()
    {
        $this->cache = new ApcCache();
    }

    /**
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheProvider $localCache
     */
    public function setCache(CacheProvider $localCache)
    {
        $this->cache = $localCache;
    }

    /**
     * load routes from class
     * 
     * @param string $className
     * @return void
     */
    public function loadRoutesFromClass($className)
    {
        $cache = new CheckableCache($this->cache);

        $key = __FUNCTION__ . ':' . md5(__CLASS__ . ':' . $className);
        $routes = $cache->get($key, $this);

        $controller = null;
        if ($routes == $this) { //not cached
            $routes = [];
            $controller = $this->controllerContainerBuilder->build($className);
            foreach ($controller->getRoutes() as $actionName => $route) {
                $routes[] = [$route->getMethod(), $route->getUri(), $actionName];
            }
            $cache->set($key, $routes, 0, new ClassModifiedChecker($className));
        }
        foreach ($routes as $route) {
            list($method, $uri, $actionName) = $route;
            $this->routes[] = [
                $method,
                $uri,
                function (Application $app, Request $request) use ($cache, $className, $actionName, $controller) {

                    $key = __FUNCTION__ . ':route:' . md5(__CLASS__ . ':' . $className . ':' . $actionName);

                    $routeInstance = $cache->get($key, $this);
                    if ($routeInstance == $this) {
                        if (!$controller) {
                            $controller = $app->controllerContainerBuilder->build($className);
                        }
                        $routeInstance = $controller->getRoute($actionName) or
                        abort(new NotFoundHttpException("action $actionName not found"));
                        $cache->set($key, $routeInstance, 0, new ClassModifiedChecker($className));
                    }
                    return ControllerContainer::dispatch($this, $className, $actionName, $routeInstance, $request);
                }];
        }
        $this->controllers[] = $className;
    }

    /**
     * load routes from path
     *
     * 被加载的文件必须以: 类名.php的形式命名
     * @param string $fromPath
     * @param string $namespace
     * @return void
     */
    public function loadRoutesFromPath($fromPath, $namespace = '')
    {
        $dir = @dir($fromPath);

        $getEach = function () use ($dir) {
            $name = $dir->read();
            if (!$name) {
                return $name;
            }
            return $name;
        };

        while (!!($entry = $getEach())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $path = $fromPath . '/' . str_replace('\\', '/', $entry);
            if (is_file($path) && substr_compare($entry, '.php', strlen($entry) - 4, 4, true) == 0) {
                $class_name = $namespace . '\\' . substr($entry, 0, strlen($entry) - 4);
                $this->loadRoutesFromClass($class_name);
            } else {
                //\Log::debug($path.' ignored');
            }
        }
    }

    /**
     * Add route
     * @param string $method
     * @param string $uri
     * @param callable $handler
     */
    public function addRoute($method, $uri, callable $handler)
    {
        $this->routes[] = [$method, $uri, $handler];
    }

    /**
     * @return ControllerContainer[]
     */
    public function getControllers()
    {
        $controllers = [];
        foreach ($this->controllers as $name) {
            $controllers[] = $this->controllerContainerBuilder->build($name);
        }
        return $controllers;
    }

    /**
     * @param Request|null $request
     * @param bool $send
     * @return Response
     */
    public function dispatch(Request $request = null, $send = true)
    {
        //  TODO 把 Route里的异常处理 ExceptionRenderer 移到这里更妥?
        if ($request == null) {
            $request = $this->make(Request::class);
        }
        $uri = $request->getRequestUri();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);


        $dispatcher = $this->getDispatcher();

        $res = $dispatcher->dispatch($request->getMethod(), $uri);
        if ($res[0] == Dispatcher::FOUND) {

            if (count($res[2])) {
                $request->attributes->add($res[2]);
            }
            $handler = $res[1];

            $response = $handler($this, $request);

            /** @var Response $response */
            if ($send) {
                $response->send();
            }
            return $response;
        } elseif ($res[0] == Dispatcher::NOT_FOUND) {
            \PhpBoot\abort(new NotFoundHttpException(), [$request->getMethod(), $uri]);
        } elseif ($res[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            \PhpBoot\abort(new MethodNotAllowedHttpException($res[1]), [$request->getMethod(), $uri]);
        } else {
            \PhpBoot\abort("unknown dispatch return {$res[0]}");
        }
    }

    /**
     * @return GroupCountBasedDispatcher
     */
    private function getDispatcher()
    {
        $routeCollector = new RouteCollector(new Std(), new GroupCountBasedDataGenerator());
        foreach ($this->routes as $route) {
            list($method, $uri, $handler) = $route;
            $routeCollector->addRoute($method, $uri, $handler);
        }
        return new GroupCountBasedDispatcher($routeCollector->getData());
    }

    /**
     * @param $className
     * @param $actionName
     * @return Route|false
     */
    private function getRoute($className, $actionName)
    {
        $key = md5($className . '::' . $actionName);
        $expiredData = null;
        $cache = new CheckableCache($this->cache);
        $route = $cache->get('route:' . $key, $this, $expiredData, false);
        if (!$route) {
            return false;
        }
        if ($route == $this) {
            return LocalAutoLock::lock($key, 60, function () use ($cache, $className, $actionName, $key) {
                $container = $this->controllerContainerBuilder->build($className);
                $route = $container->getRoute($actionName);
                $cache->set('route:' . $key, $route, 0, new ClassModifiedChecker($className));
                return $route;
            }, function () use ($expiredData) {
                return $expiredData;
            });
        }
        return $route;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable $callable Function to call.
     * @param array $parameters Parameters to use.
     *
     * @return mixed Result of the function.
     *
     * @throws InvocationException Base exception class for all the sub-exceptions below.
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = array())
    {
        return $this->container->call($callable, $parameters);
    }

    public function make($name, array $parameters = [])
    {
        return $this->container->make($name, $parameters);
    }

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var ControllerContainerBuilder
     */
    protected $controllerContainerBuilder;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * [
     *      [method, uri, loader]
     * ]
     * @var array
     */
    protected $routes = [];

    /**
     * @var string[]
     */
    protected $controllers = [];

}