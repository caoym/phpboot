<?php
namespace PhpBoot;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ApcCache;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use PhpBoot\Annotation\Controller\ControllerMetaLoader;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\Route;
use PhpBoot\Lock\LocalAutoLock;
use Pimple\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements ContainerInterface
{
    /**
     * Application constructor.
     * @param string|array $conf
     */
    public function __construct($conf)
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions($conf);
        $this->container = $builder->build();
        $this->cache = new CheckableCache(new ApcCache());
    }

    public function make($className, $params=[]){
        return $this->container->make($className, $params);
    }

    /**
     * @param string $className
     * @return void
     */
    public function loadRoutesFromClass($className)
    {
        $this->routeLoaders[$className] = function()use($className){
            $loader = new ControllerMetaLoader();
            $container = $loader->loadFromClass($className);
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
        $data =  $this->cache->get($key, $this, $expiredData, false);
        if($data == $this){
            $data = LocalAutoLock::lock(
                $key,
                60,
                function ()use($key){
                    $routeCollector = new RouteCollector(new Std(), new GroupCountBasedDataGenerator());
                    foreach ($this->routeLoaders as $loader){
                        $containers[] = $loader();
                        /**@var ControllerContainer[] $containers*/
                        foreach ($containers as $container){
                            foreach ($container->getRoutes() as $actionName=>$route){
                                $routeCollector->addRoute($route->getMethod(), $route->getUri(), [$container->getClassName(), $actionName]);
                                $this->cache->set('route:'.md5($container->getClassName().'::'.$actionName),$route, new FileExpiredChecker($container->getFileName()));
                            }
                        }
                    }
                    $this->cache->set($key, $routeCollector->getData());
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
        $uri = $request->getRequestUri();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $dispatcher = $this->getDispatcher();
        if(!$dispatcher){
            fail(new NotFoundHttpException('none'), [$request->getMethod(), $uri]);
        }

        $res = $dispatcher->dispatch($request->getMethod(), $uri);
        if($res[0] == Dispatcher::FOUND){

            list($className, $actionName) = $res[1];
            $route = $this->getRoute($className, $actionName);
            if(!$route){
                fail(new NotFoundHttpException('dirty data'), [$request->getMethod(), $uri]);
            }
            $request = Request::createFromGlobals();
            $request->attributes->add($res[2]);
            $response = ControllerContainer::dispatch($className, $actionName,$route, $this, $request);

            /** @var Response $response */
            if($send){
                $response->send();
            }
            return $response;
        }elseif ($res[0] == Dispatcher::NOT_FOUND){
            fail(new NotFoundHttpException(), [$request->getMethod(), $uri]);
        }elseif ($res[0] == Dispatcher::METHOD_NOT_ALLOWED){
            fail(new MethodNotAllowedHttpException($res[1]), [$request->getMethod(), $uri]);
        }else{
            fail("unknown dispatch return {$res[0]}");
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
        $route = $this->cache->get('route:'.$key, $this, $expiredData, false);
        if(!$route){
            return false;
        }
        if($route == $this){
            return LocalAutoLock::lock($key, 60, function()use($className, $actionName, $key){
                $container = new ControllerContainer($className);
                $route = $container->getRoute($actionName);
                $this->cache->set('route:'.$key, $route, new FileExpiredChecker($container->getFileName()));
                return $route;
            }, function()use($expiredData){
                return $expiredData;
            });
        }
        return $route;
    }

    private $cache;

    /**
     * @var callable[]
     */
    private $routeLoaders = [];

    private $dispatcher;
    /**
     * @var Container
     */
    private $container;

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
}