<?php

namespace PhpBoot\Tests;

use PhpBoot\Application;
use PhpBoot\Docgen\Swagger\Swagger;
use PhpBoot\Tests\Utils\RpcTestController;

class SwaggerTest extends TestCase
{
    public function testSwagger()
    {
        $app = Application::createByDefault();
        $app->loadRoutesFromClass(RpcTestController::class);

        $swagger = new Swagger($app);
        $swagger->info->title = 'test title';
        $swagger->info->description = 'the test description';

        $swagger->appendControllers($app, $app->getControllers());

        $actual = $swagger->toArray();

        self::assertEquals($this->expected, $actual);
    }


    private $expected = array (
        'swagger' => '2.0',
        'info' =>
            array (
                'title' => 'test title',
                'description' => 'the test description',
                'version' => '',
            ),
        'host' => 'localhost',
        'basePath' => '/',
        'schemes' =>
            array (
                0 => 'http',
            ),
        'consumes' =>
            array (
                0 => 'application/json',
            ),
        'produces' =>
            array (
                0 => 'application/json',
            ),
        'paths' =>
            array (
                '/tests/{intArg}/testRequestGet' =>
                    array (
                        'get' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'type' => 'integer',
                                                'maximum' => 2,
                                                'minimum' => 1,
                                                'required' => true,
                                                'in' => 'path',
                                                'name' => 'intArg',
                                                'description' => '{@v min:1|max:2}',
                                            ),
                                        1 =>
                                            array (
                                                'type' => 'boolean',
                                                'required' => true,
                                                'in' => 'query',
                                                'name' => 'boolArg',
                                                'description' => '',
                                            ),
                                        2 =>
                                            array (
                                                'type' => 'number',
                                                'maximum' => 2.1000000000000001,
                                                'minimum' => 1.1000000000000001,
                                                'required' => true,
                                                'in' => 'query',
                                                'name' => 'floatArg',
                                                'description' => '{@v min:1.1|max:2.1}',
                                            ),
                                        3 =>
                                            array (
                                                'type' => 'string',
                                                'required' => true,
                                                'in' => 'query',
                                                'name' => 'strArg',
                                                'description' => '',
                                            ),
                                        4 =>
                                            array (
                                                '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                                'in' => 'query',
                                                'name' => 'objArg',
                                                'description' => '',
                                                'required' => true,
                                            ),
                                        5 =>
                                            array (
                                                'type' => 'array',
                                                'items' =>
                                                    array (
                                                        'type' => 'string',
                                                    ),
                                                'in' => 'query',
                                                'name' => 'arrArg',
                                                'description' => '',
                                                'required' => true,
                                            ),
                                        6 =>
                                            array (
                                                'required' => true,
                                                'in' => 'query',
                                                'name' => 'mixedArg',
                                                'description' => '',
                                            ),
                                        7 =>
                                            array (
                                                'type' => 'string',
                                                'required' => false,
                                                'default' => 'default',
                                                'in' => 'query',
                                                'name' => 'defaultArg',
                                                'description' => '',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRequestGetRes',
                                                    ),
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
                '/tests/testRequestPost' =>
                    array (
                        'post' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRequestPostReq',
                                                    ),
                                                'name' => 'body',
                                                'in' => 'body',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRequestPostRes',
                                                    ),
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
                '/tests/testRequestPostWithBind' =>
                    array (
                        'post' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'type' => 'integer',
                                                'required' => true,
                                                'in' => 'query',
                                                'name' => 'intArg',
                                                'description' => '{@bind request.query.intArg}',
                                            ),
                                        1 =>
                                            array (
                                                'type' => 'boolean',
                                                'required' => true,
                                                'in' => 'header',
                                                'name' => 'x-boolArg',
                                                'description' => '{@bind request.headers.x-boolArg}',
                                            ),
                                        2 =>
                                            array (
                                                'type' => 'number',
                                                'required' => true,
                                                'in' => 'cookie',
                                                'name' => 'x-floatArg',
                                                'description' => '{@bind request.cookies.x-floatArg}',
                                            ),
                                        3 =>
                                            array (
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRequestPostWithBindReq0',
                                                    ),
                                                'name' => 'body',
                                                'in' => 'body',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRequestPostWithBindRes',
                                                    ),
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
                '/tests/testResponse' =>
                    array (
                        'post' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestResponseReq',
                                                    ),
                                                'name' => 'body',
                                                'in' => 'body',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestResponseRes0',
                                                    ),
                                            ),
                                        404 =>
                                            array (
                                                'description' => 'NotFoundHttpException: ',
                                            ),
                                        400 =>
                                            array (
                                                'description' => 'BadRequestHttpException: ',
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
                '/tests/testRefRequestWithoutBind' =>
                    array (
                        'post' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRefRequestWithoutBindReq',
                                                    ),
                                                'name' => 'body',
                                                'in' => 'body',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                        '$ref' => '#/definitions/RpcTestControllerTestRefRequestWithoutBindRes',
                                                    ),
                                            ),
                                        404 =>
                                            array (
                                                'description' => 'NotFoundHttpException: ',
                                            ),
                                        400 =>
                                            array (
                                                'description' => 'BadRequestHttpException: ',
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
                '/tests/testRequestWithoutFile' =>
                    array (
                        'post' =>
                            array (
                                'tags' =>
                                    array (
                                        0 => '',
                                    ),
                                'summary' => '',
                                'description' => '',
                                'consumes' =>
                                    array (
                                        0 => 'multipart/form-data',
                                    ),
                                'parameters' =>
                                    array (
                                        0 =>
                                            array (
                                                'type' => 'file',
                                                'required' => true,
                                                'in' => 'formData',
                                                'name' => 'file1',
                                                'description' => '{@bind request.files.file1}',
                                            ),
                                    ),
                                'responses' =>
                                    array (
                                        200 =>
                                            array (
                                                'description' => '',
                                                'schema' =>
                                                    array (
                                                    ),
                                            ),
                                    ),
                                'schemes' =>
                                    array (
                                        0 => 'http',
                                    ),
                                'deprecated' => false,
                            ),
                    ),
            ),
        'definitions' =>
            array (
                'PhpBoot\\Tests\\Utils\\RpcTestEntity1' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'intArg',
                                1 => 'boolArg',
                                2 => 'floatArg',
                                3 => 'strArg',
                            ),
                        'properties' =>
                            array (
                                'intArg' =>
                                    array (
                                        'type' => 'integer',
                                        'description' => '',
                                    ),
                                'boolArg' =>
                                    array (
                                        'type' => 'boolean',
                                        'description' => '',
                                    ),
                                'floatArg' =>
                                    array (
                                        'type' => 'number',
                                        'description' => '',
                                    ),
                                'strArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                                'defaultArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                            ),
                        'description' => '',
                    ),
                'RpcTestControllerTestRequestGetRes' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'refArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                                'data' =>
                                    array (
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRequestPostReq' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'intArg',
                                1 => 'boolArg',
                                2 => 'floatArg',
                                3 => 'strArg',
                                4 => 'objArg',
                                5 => 'arrArg',
                            ),
                        'properties' =>
                            array (
                                'intArg' =>
                                    array (
                                        'type' => 'integer',
                                        'description' => '',
                                    ),
                                'boolArg' =>
                                    array (
                                        'type' => 'boolean',
                                        'description' => '',
                                    ),
                                'floatArg' =>
                                    array (
                                        'type' => 'number',
                                        'description' => '',
                                    ),
                                'strArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                                'objArg' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                        'description' => '',
                                    ),
                                'arrArg' =>
                                    array (
                                        'type' => 'array',
                                        'items' =>
                                            array (
                                                'type' => 'string',
                                            ),
                                        'description' => '',
                                    ),
                                'defaultArg' =>
                                    array (
                                        'type' => 'string',
                                        'default' => 'default',
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRequestPostRes' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'refArg' =>
                                    array (
                                        'description' => '',
                                    ),
                                'data' =>
                                    array (
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRequestPostWithBindReq' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'strArg',
                            ),
                        'properties' =>
                            array (
                                'strArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '{@bind request.request.strArg.strArg}',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRequestPostWithBindReq0' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'objArg',
                                1 => 'arrArg',
                            ),
                        'properties' =>
                            array (
                                'strArg' =>
                                    array (
                                        '$ref' => '#/definitions/RpcTestControllerTestRequestPostWithBindReq',
                                    ),
                                'objArg' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                        'description' => '',
                                    ),
                                'arrArg' =>
                                    array (
                                        'type' => 'array',
                                        'items' =>
                                            array (
                                                'type' => 'string',
                                            ),
                                        'description' => '',
                                    ),
                                'defaultArg' =>
                                    array (
                                        'type' => 'string',
                                        'default' => 'default',
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRequestPostWithBindRes' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'refArg' =>
                                    array (
                                        'description' => '',
                                    ),
                                'data' =>
                                    array (
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestResponseReq' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'intArg',
                            ),
                        'properties' =>
                            array (
                                'intArg' =>
                                    array (
                                        'type' => 'integer',
                                        'description' => '',
                                    ),
                            ),
                    ),
                'PhpBoot\\Tests\\Utils\\RpcTestEntity2' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'intArg',
                                1 => 'boolArg',
                                2 => 'floatArg',
                                3 => 'strArg',
                                4 => 'objArg',
                                5 => 'arrArg',
                            ),
                        'properties' =>
                            array (
                                'intArg' =>
                                    array (
                                        'type' => 'integer',
                                        'description' => '',
                                    ),
                                'boolArg' =>
                                    array (
                                        'type' => 'boolean',
                                        'description' => '',
                                    ),
                                'floatArg' =>
                                    array (
                                        'type' => 'number',
                                        'description' => '',
                                    ),
                                'strArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                                'objArg' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                    ),
                                'arrArg' =>
                                    array (
                                        'type' => 'array',
                                        'items' =>
                                            array (
                                                '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                            ),
                                    ),
                                'defaultArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                            ),
                        'description' => '',
                    ),
                'RpcTestControllerTestResponseRes' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'bindArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '{@bind response.content.bindArg.bindArg}',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestResponseRes0' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'objArg' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                        'description' => '',
                                    ),
                                'arrStrArg' =>
                                    array (
                                        'type' => 'array',
                                        'items' =>
                                            array (
                                                'type' => 'string',
                                            ),
                                        'description' => '',
                                    ),
                                'mixedArg' =>
                                    array (
                                        'description' => '',
                                    ),
                                'data' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity2',
                                        'description' => 'response.content.data',
                                    ),
                                'bindArg' =>
                                    array (
                                        '$ref' => '#/definitions/RpcTestControllerTestResponseRes',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRefRequestWithoutBindReq' =>
                    array (
                        'type' => 'object',
                        'required' =>
                            array (
                                0 => 'intArg',
                            ),
                        'properties' =>
                            array (
                                'intArg' =>
                                    array (
                                        'type' => 'integer',
                                        'description' => '',
                                    ),
                            ),
                    ),
                'RpcTestControllerTestRefRequestWithoutBindRes' =>
                    array (
                        'type' => 'object',
                        'properties' =>
                            array (
                                'boolArg' =>
                                    array (
                                        'type' => 'boolean',
                                        'description' => '',
                                    ),
                                'floatArg' =>
                                    array (
                                        'type' => 'number',
                                        'description' => '',
                                    ),
                                'strArg' =>
                                    array (
                                        'type' => 'string',
                                        'description' => '',
                                    ),
                                'objArg' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity1',
                                        'description' => '',
                                    ),
                                'arrStrArg' =>
                                    array (
                                        'type' => 'array',
                                        'items' =>
                                            array (
                                                'type' => 'string',
                                            ),
                                        'description' => '',
                                    ),
                                'mixedArg' =>
                                    array (
                                        'description' => '',
                                    ),
                                'data' =>
                                    array (
                                        '$ref' => '#/definitions/PhpBoot\\Tests\\Utils\\RpcTestEntity2',
                                        'description' => '',
                                    ),
                            ),
                    ),
            ),
        'tags' =>
            array (
                0 =>
                    array (
                        'name' => '',
                        'description' => '',
                    ),
            ),
    );


}