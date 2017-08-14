<?php

namespace PhpBoot\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpBoot\Controller\ControllerContainerBuilder;
use PhpBoot\RPC\RpcProxy;
use PhpBoot\Tests\Utils\RpcTestController;
use PhpBoot\Tests\Utils\RpcTestEntity1;
use PhpBoot\Tests\Utils\RpcTestEntity2;

class RpcProxyTest extends TestCase
{
    public function testRequestGet()
    {
        $builder = $this->app->get(ControllerContainerBuilder::class);
        $rpc = $this->app->make(RpcProxy::class,
            [
                'builder'=>$builder,
                'interface'=>RpcTestController::class,
                'prefix'=>'http://localhost/'
            ]
        );

        $controller = $builder->build(RpcTestController::class);
        $refArg = null;
        $request = $rpc->createRequest(__FUNCTION__, $controller->getRoute(__FUNCTION__), [
            1,
            true,
            3.1,
            '4',
            new RpcTestEntity1(),
            ['6'],
            &$refArg,
            'mixed'
        ]);
        self::assertEquals(new Request('GET', 'http://localhost/tests/1/testRequestGet?'.http_build_query([
                    'boolArg' => true,
                    'floatArg' => 3.1,
                    'strArg' => '4',
                    'objArg' => new RpcTestEntity1(),
                    'arrArg' => ['6'],
                    'mixedArg' => 'mixed',
                    'defaultArg' => 'default',

                ]
            )), $request);
    }

    public function testRequestPost()
    {
        $builder = $this->app->get(ControllerContainerBuilder::class);
        $rpc = $this->app->make(RpcProxy::class,
            [
                'builder'=>$builder,
                'interface'=>RpcTestController::class,
                'prefix'=>'http://localhost/'
            ]
        );
        $controller = $builder->build(RpcTestController::class);
        $refArg = null;
        $request = $rpc->createRequest(__FUNCTION__, $controller->getRoute(__FUNCTION__), [
            1,
            true,
            3.1,
            '4',
            new RpcTestEntity1(),
            ['6'],
            &$refArg
        ]);

        $expected = new Request('POST', 'http://localhost/tests/testRequestPost', [], json_encode([
            'intArg' => 1,
            'boolArg' => true,
            'floatArg' => 3.1,
            'strArg' => '4',
            'objArg' => new RpcTestEntity1(),
            'arrArg' => ['6'],
            'defaultArg' => 'default'
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        self::assertEquals($expected->getMethod(), $request->getMethod());
        self::assertEquals($expected->getHeaders(), $request->getHeaders());
        self::assertEquals($expected->getUri(), $request->getUri());
        self::assertEquals((string)$expected->getBody(), (string)$request->getBody());

    }

    public function testRequestPostWithBind()
    {
        $builder = $this->app->get(ControllerContainerBuilder::class);
        $rpc = $this->app->make(RpcProxy::class,
            [
                'builder'=>$builder,
                'interface'=>RpcTestController::class,
                'prefix'=>'http://localhost/'
            ]
        );
        $controller = $builder->build(RpcTestController::class);
        $refArg = null;
        $request = $rpc->createRequest(__FUNCTION__, $controller->getRoute(__FUNCTION__), [
            1,
            true,
            3.1,
            '4',
            new RpcTestEntity1(),
            ['6'],
            &$refArg
        ]);

        $expected = new Request('POST', 'http://localhost/tests/testRequestPostWithBind?intArg=1', [
            'x-boolArg'=>1,
            'Cookie'=>'x-floatArg=3.1'
        ],
            json_encode([
            'strArg' => ['strArg'=>'4'],
            'objArg' => new RpcTestEntity1(),
            'arrArg' => ['6'],
            'defaultArg' => 'default'
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        self::assertEquals($expected->getMethod(), $request->getMethod());
        self::assertEquals($expected->getHeaders(), $request->getHeaders());
        self::assertEquals($expected->getUri(), $request->getUri());
        self::assertEquals((string)$expected->getBody(), (string)$request->getBody());
    }

    public function testResponse()
    {
        $builder = $this->app->get(ControllerContainerBuilder::class);
        $rpc = $this->app->make(RpcProxy::class,
            [
                'builder'=>$builder,
                'interface'=>RpcTestController::class,
                'prefix'=>'http://localhost/'
            ]
        );
        $controller = $builder->build(RpcTestController::class);
        $refArg = null;
        $response = new Response(200, [
            'x-boolArg'=>1,
            'x-floatArg'=>'3.1'
        ],
        json_encode([
            'bindArg' => ['bindArg'=>'4'],
            'objArg' => new RpcTestEntity1(),
            'arrArg' => [new RpcTestEntity1()],
            'arrStrArg' => ['6'],
            'mixedArg' => 'any',
            'data'=>new RpcTestEntity2()
            ]
        ));

        $intArg = $boolArg = $floatArg = $bindArg = $objArg = $arrStrArg = $mixedArg = null;

        $args = [
            $intArg,
            &$boolArg,
            &$floatArg,
            &$bindArg,
            &$objArg,
            &$arrStrArg,
            &$mixedArg
        ];
        /**@var RpcProxy $rpc*/
        $return = $rpc->mapResponse(__FUNCTION__, $controller->getRoute(__FUNCTION__), $response, $args);

        $expected = new RpcTestEntity2();
        self::assertEquals($expected, $return);

        self::assertSame($intArg, null);
        self::assertSame($boolArg, true);
        self::assertSame($floatArg, 3.1);
        self::assertSame($bindArg, '4');
        self::assertEquals($objArg, new RpcTestEntity1());
        self::assertSame($arrStrArg, ['6']);
        self::assertSame($mixedArg, 'any');
    }

    // TODO 实现文件精确
}
