<?php

namespace PhpBoot\Tests;
use PhpBoot\Annotation\Controller\ControllerMetaLoader;
use PhpBoot\Controller\ControllerBuilder;

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
     * @param int $arg0
     * @param $arg1
     * @param $arg2 the arg 2
     * @param string $arg3 the arg 3
     * @throws \Exception the Exception
     * @throws \RuntimeException the RuntimeException
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
        $loader = new ControllerMetaLoader();
        $actual = $loader->loadFromClass(ControllerTest::class);
        $expected = new ControllerBuilder(ControllerTest::class);
        $this->assertEquals($expected, $actual);
    }
}