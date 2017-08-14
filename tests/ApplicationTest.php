<?php

namespace PhpBoot\Tests;

use PhpBoot\Application;
use PhpBoot\Controller\HookInterface;
use PhpBoot\Controller\Hooks\Cors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestHook1 implements HookInterface
{

    public function handle(Request $request, callable $next)
    {
        $res =  $next($request);
        /**@var Response $res*/
        $res->setContent($res->getContent()."TestHook1");
        return $res;
    }
}

class TestHook2 implements HookInterface
{

    public function handle(Request $request, callable $next)
    {
        $res =  $next($request);
        /**@var Response $res*/
        $res->setContent($res->getContent()."TestHook2");
        return $res;
    }
}

/**
 * Class HookControllerTest
 * @path /
 */
class HookControllerTest
{
    /**
     * @route GET /testAnnotationHooks
     * @hook TestHook1
     * @hook TestHook2
     * @return string
     */
    public function test()
    {
        return "route";
    }
}

class ApplicationTest extends TestCase
{
    public function testAddHooks()
    {
        $this->app->addRoute('GET', '/testHooks',
            function(Application $app, Request $req){
                $res = new Response();
                $res->setContent("route");
                return $res;
            },
            [TestHook1::class, TestHook2::class]
        );
        $req = new Request([], [], [], [], [], ['REQUEST_METHOD'=>'GET', 'REQUEST_URI'=>'/testHooks'], []);
        $res = $this->app->dispatch($req, false);
        self::assertEquals($res->getContent(), "routeTestHook2TestHook1");
    }

    public function testAnnotationHooks()
    {
        $this->app->loadRoutesFromClass(HookControllerTest::class);
        $req = new Request([], [], [], [], [], ['REQUEST_METHOD'=>'GET', 'REQUEST_URI'=>'/testAnnotationHooks'], []);
        $res = $this->app->dispatch($req, false);
        self::assertEquals($res->getContent(), "\"route\"TestHook2TestHook1");
    }

    public function testWithBadRequest()
    {

    }

    public function testWithValidation()
    {

    }

}