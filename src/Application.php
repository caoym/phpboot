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
use PhpBoot\Annotation\ContainerBuilder;
use PhpBoot\Controller\ControllerContainerBuilder;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\ClassModifiedChecker;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\Route;
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
    static public function createByDefault($conf=[]){
        $builder = new DIContainerBuilder();

        $default = [
            'AppName'=>'App',
            LoggerInterface::class  =>  \DI\object(\Monolog\Logger::class)->constructor(\DI\get('AppName')),
            Request::class          =>  \DI\factory([Request::class, 'createFromGlobals']),
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
    public function make($name, array $parameters = []){
        return $this->container->make($name, $parameters);
    }

    /**
     * @param string $className
     * @return void
     */
    public function loadRoutesFromClass($className)
    {
        $this->routeLoaders[$className] = function()use($className){
            $container = $this->controllerContainerBuilder->build($className);
            return [$container];
        };
    }
    /**
     * @return ControllerContainer[]
     */
    public function getControllers()
    {
        $key = 'controllers:'.md5(serialize(array_keys($this->routeLoaders)));
        return LocalAutoLock::lock($key, 60, function (){
            $res = [];
            foreach ($this->routeLoaders as $loader){
                $res = array_merge($res, $loader());
            }
            return $res;
        });
    }

    /**
     * @return bool|GroupCountBasedDispatcher
     */
    private function getDispatcher()
    {
        if($this->dispatcher){
            return $this->dispatcher;
        }
        $key = 'controllers:'.md5(serialize(array_keys($this->routeLoaders)));
        $expiredData = null;
        $cache = new CheckableCache($this->cache);
        $data =  $cache->get($key, $this, $expiredData, false);
        if($data == $this){
            $data = LocalAutoLock::lock(
                $key,
                60,
                function ()use($cache, $key){
                    $routeCollector = new RouteCollector(new Std(), new GroupCountBasedDataGenerator());
                    foreach ($this->routeLoaders as $loader){
                        $containers = $loader();
                        /**@var ControllerContainer[] $containers*/
                        foreach ($containers as $container){
                            foreach ($container->getRoutes() as $actionName=>$route){
                                $routeCollector->addRoute($route->getMethod(), $route->getUri(), [$container->getClassName(), $actionName]);
                                $cache->set('route:'.md5($container->getClassName().'::'.$actionName),$route, 0, new ClassModifiedChecker($container->getClassName()));
                            }
                        }
                    }
                    $cache->set($key, $routeCollector->getData());
                    return $routeCollector->getData();
                },
                function()use($expiredData){
                    return $expiredData;
                });
        }
        if(!$data){
            return false;
        }
        $this->dispatcher = new GroupCountBasedDispatcher($data);
        return $this->dispatcher;
    }
    public function dispatch(Request $request = null, $send = true)
    {
        if ($request == null){
            $request = $this->make(Request::class);
        }
        $uri = $request->getRequestUri();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $dispatcher = $this->getDispatcher();
        if(!$dispatcher){
            \PhpBoot\abort(new NotFoundHttpException('none'), [$request->getMethod(), $uri]);
        }

        $res = $dispatcher->dispatch($request->getMethod(), $uri);
        if($res[0] == Dispatcher::FOUND){

            list($className, $actionName) = $res[1];
            $route = $this->getRoute($className, $actionName);
            if(!$route){
                \PhpBoot\abort(new NotFoundHttpException('dirty data'), [$request->getMethod(), $uri]);
            }
            $request = Request::createFromGlobals();
            $request->attributes->add($res[2]);

            $response = ControllerContainer::dispatch($this, $className, $actionName, $route, $request);

            /** @var Response $response */
            if($send){
                $response->send();
            }
            return $response;
        }elseif ($res[0] == Dispatcher::NOT_FOUND){
            \PhpBoot\abort(new NotFoundHttpException(), [$request->getMethod(), $uri]);
        }elseif ($res[0] == Dispatcher::METHOD_NOT_ALLOWED){
            \PhpBoot\abort(new MethodNotAllowedHttpException($res[1]), [$request->getMethod(), $uri]);
        }else{
            \PhpBoot\abort("unknown dispatch return {$res[0]}");
        }
    }

    /**
     * @param $className
     * @param $actionName
     * @return Route|false
     */
    private function getRoute($className, $actionName)
    {
        $key = md5($className.'::'.$actionName);
        $expiredData = null;
        $cache = new CheckableCache($this->cache);
        $route = $cache->get('route:'.$key, $this, $expiredData, false);
        if(!$route){
            return false;
        }
        if($route == $this){
            return LocalAutoLock::lock($key, 60, function()use($cache, $className, $actionName, $key){
                $container = $this->controllerContainerBuilder->build($className);
                $route = $container->getRoute($actionName);
                $cache->set('route:'.$key, $route, 0, new ClassModifiedChecker($className));
                return $route;
            }, function()use($expiredData){
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
     * @inject
     * @var Container
     */
    public $container;

    /**
     * @inject
     * @var ControllerContainerBuilder
     */
    public $controllerContainerBuilder;

    /**
     * @var CacheProvider
     */
    private $cache;

    /**
     * @var callable[]
     */
    private $routeLoaders = [];

    private $dispatcher;

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
}