<?php

namespace PhpBoot\Tests;


use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Entity\EntityBuilder;
use PhpBoot\Metas\PropertyMeta;

/**
 * Class MetaLoaderTest
 */
class MetaLoaderTest{

    /**
     * property1
     * @var string
     */
    public $property1;

    /**
     * @var string
     */
    public $property2='default0';

    public $property3=1;

}

class EntityMetaLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testAll()
    {
        $loader = new EntityMetaLoader();
        $actual = $loader->loadFromClass(MetaLoaderTest::class);

        $expected = new EntityBuilder(MetaLoaderTest::class);
        $expected->setSummary('Class MetaLoaderTest');
        $expected->setFileName(__FILE__);
        $expected->setProperty('property1', new PropertyMeta('property1', 'string', null,null,null,'property1'));
        $expected->setProperty('property2', new PropertyMeta('property2', 'string', true,'default0'));
        $expected->setProperty('property3', new PropertyMeta('property3', null, true,1));
        self::assertEquals($expected, $actual);
    }
}