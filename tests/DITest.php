<?php

namespace PhpBoot\Tests;

use PhpBoot\DI\ContainerBuilder;

class DITestClass{
    /**
     * @inject
     * @var \stdClass
     */
    public $test1;
}

class DITest extends TestCase
{
    public function testMakeDefault()
    {
        $builder = new ContainerBuilder();
        $container = $builder->build();
        $res = $container->make(DITestClass::class, []);
        return $res;
    }
}