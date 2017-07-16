<?php
namespace PhpBoot;

use Doctrine\Common\Cache\ApcCache;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use PhpBoot\Annotation\Controller\ControllerMetaLoader;
use PhpBoot\Cache\CheckableCache;
use PhpBoot\Cache\FileExpiredChecker;
use PhpBoot\Controller\ControllerBuilder;
use PhpBoot\Controller\Route;
use PhpBoot\Lock\LocalAutoLock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application
{
    public function __construct()
    {
        $this->cache = new CheckableCache(new ApcCache());
        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBasedDataGenerator());
    }

    public function make($className){
        return new $className;
    }


    /**
     * @param string $className
     * @return void
     */
    public function loadRoutesFromClass($className)
    {
        $this->routeLoaders[$className] = function()use($className){
            $loader = new ControllerMetaLoader();
            $builder = $loader->loadFromClass($className);
            return [$builder];
        };
    }
    /**
     * @return ControllerBuilder[]
     */
    public function getRoutes()
    {
        $key = 'routes:'.md5(serialize(array_keys($this->routeLoaders)));
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
        $key = 'routes:'.md5(serialize(array_keys($this->routeLoaders)));
        $expiredData = null;
        $data =  $this->cache->get($key, $this, $expiredData, false);
        if($data == $this){
            $data = LocalAutoLock::lock(
                $key,
                60,
                function ()use($key){
                    $routeCollector = new RouteCollector(new Std(), new GroupCountBasedDataGenerator());
                    foreach ($this->routeLoaders as $loader){
                        $builders[] = $loader();
                        /**@var ControllerBuilder[] $builders*/
                        foreach ($builders as $builder){
                            foreach ($builder->getRoutes() as $actionName=>$route){
                                $routeCollector->addRoute($route->getMethod(), $route->getUri(), [$builder->getClassName(), $actionName]);
                                $this->cache->set('route:'.md5($builder->getClassName().'::'.$actionName),$route, new FileExpiredChecker($builder->getFileName()));
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
        return new GroupCountBasedDispatcher($data);
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
            $response = ControllerBuilder::dispatch($className, $actionName,$route, $this, $request);

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
                $builder = new ControllerBuilder($className);
                $route = $builder->getRoute($actionName);
                $this->cache->set('route:'.$key, $route, new FileExpiredChecker($builder->getFileName()));
                return $route;
            }, function()use($expiredData){
                return $expiredData;
            });
        }
        return $route;
    }
    /**
     * @var RouteCollector
     */
    private $routeCollector;

    private $cache;

    /**
     * @var callable[]
     */
    private $routeLoaders = [];
}