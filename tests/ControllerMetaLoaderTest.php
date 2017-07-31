<?php

namespace PhpBoot\Tests;
use PhpBoot\Application;
use PhpBoot\Controller\ControllerContainerBuilder;
use PhpBoot\Controller\ControllerContainer;
use PhpBoot\Controller\HookInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HookTest1 implements HookInterface{

    /**
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next)
    {
        return $next();
    }
}
class HookTest2 implements HookInterface{

    /**
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next)
    {
        return $next();
    }
}
/**
 * Class ControllerTest
 *
 * ControllerTest
 *
 * @path /test-path
 */
class ControllerTest
{
    /**
     * @route GET /route1/{arg0}
     * @param string $arg0
     * @param $arg1 {@bind request.argX}
     * @param $arg2 the arg 2 {@bind response.content.total}
     * @param int $arg3 {@v min:100|max:200 } the arg 3
     * @throws \Exception the Exception
     * @throws \RuntimeException
     * @hook HookTest2
     * @hook HookTest1
     * @return bool the return
     */
    public function route1($arg0, $arg1, &$arg2, $arg3, $arg4='default')
    {

    }
}

class ControllerMetaLoaderTest extends TestCase
{
    public function testLoad()
    {
        $builder = $this->app->make(ControllerContainerBuilder::class);
        $actual = $builder->build(ControllerTest::class);
        $expected = new ControllerContainer(ControllerTest::class);
        //TODO $this->assertEquals($expected, $actual);
    }
}