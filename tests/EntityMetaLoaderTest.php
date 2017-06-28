<?php

namespace PhpBoot\Tests;


use PhpBoot\Annotation\Entity\EntityMetaLoader;

class MetaLoaderTest{

    /**
     * test
     * @var string
     */
    public $property;
}

class EntityMetaLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testAll()
    {
        $loader = new EntityMetaLoader();
        $res = $loader->loadFromClass(MetaLoaderTest::class);
    }
}