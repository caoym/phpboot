<?php

namespace PhpBoot\Tests;

use PhpBoot\Application;
use PhpBoot\Docgen\Swagger\Swagger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SwaggerTestEntity1
 *
 * the SwaggerTestEntity1
 */
class SwaggerTestEntity1
{
    /**
     * the string property
     * @var string
     */
    public $property0;

    /**
     * the array property
     * @var int[]
     */
    public $property1;

}


class SwaggerTestEntity2
{
    /**
     * the entity property
     *
     * @var SwaggerTestEntity1
     */
    public $property0;
}

/**
 * Class SwaggerTestController
 *
 * SwaggerTestController
 *
 * @path /swagger-test
 *
 */
class SwaggerTestController
{
    /**
     * get test 1
     *
     * the get test 1
     *
     * @route GET /test1
     * @param string $param1 the param 1
     * @param bool $param2 the param 2
     * @param bool $bind1 {@bind response.content.bind1}
     * @throws NotFoundHttpException not found
     * @return SwaggerTestEntity1 the return {@bind response.content.bind2}
     */
    public function getTest1($param1, $param2, &$bind1)
    {

    }

    /**
     * post test 2
     *
     * the post test 2
     *
     * @route POST /test2
     * @param string $param1 the param 1
     * @param SwaggerTestEntity2 $param2 the param 2
     * @throws NotFoundHttpException not found
     */
    public function getTest2($param1, $param2)
    {

    }

    /**
     * post test 3
     *
     * the post test 3
     *
     * @route POST /test3
     * @param SwaggerTestEntity2 $param1 {@bind request.request}
     */
    public function getTest3($param1)
    {

    }
}

class SwaggerTest extends TestCase
{
    public function testSwagger()
    {
        $app = Application::createByDefault();
        $app->loadRoutesFromClass(SwaggerTestController::class);

        $swagger = new Swagger($app);
        $swagger->info->title = 'test title';
        $swagger->info->description = 'the test description';

        $swagger->appendControllers($app, $app->getControllers());

        $json = $swagger->toJson();
        //TODO TEST
        return $json;

    }
}