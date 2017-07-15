<?php
namespace PhpBoot;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use PhpBoot\Annotation\Controller\ControllerMetaLoader;
use Symfony\Component\HttpFoundation\Request;

class Application
{
    public function make($className){
        return new $className;
    }
    public function getRoutesFromClass($name)
    {
        $loader = new ControllerMetaLoader();
        $builder = $loader->loadFromClass($name);
    }
    public function addRoute($httpMethod, $uri, $handler, $summary = '', $description='')
    {

    }
    public function dispatch(Request $request = null)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r){

        });
        $uri = $request->getRequestUri();
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $res = $dispatcher->dispatch($request->getMethod(), $uri);
    }

}