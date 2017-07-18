<?php

namespace PhpBoot\Tests;


use PhpBoot\Annotation\Entity\EntityMetaLoader;
use PhpBoot\Entity\ArrayBuilder;
use PhpBoot\Entity\EntityBuilder;
use PhpBoot\Entity\ScalarTypeBuilder;
use PhpBoot\Metas\PropertyMeta;

/**
 * Class LoadTest
 */
class LoadTest{

    /**
     * property1
     * @var string[]
     */
    public $property1;

    /**
     * @var string
     * @v email
     */
    public $property2='default0';

    public $property3=1;
}


class BuildScalarTypeTest{
    /**
     * @var int
     * @v max:101
     */
    public $property1;
}

class BuildScalarTypeArrayTest{
    /**
     * @var int[]
     * @v max:101 *
     */
    public $property1;
}

class BuildEntityTest{
    /**
     * @var BuildScalarTypeTest
     */
    public $property1;
}


class BuildEntityArrayTest{
    /**
     * @var BuildScalarTypeTest[]
     */
    public $property1;
}

class EntityMetaLoaderTest extends TestCase
{

    public function testLoad()
    {
        $loader = new EntityMetaLoader();
        $actual = $loader->loadFromClass(LoadTest::class);

        $expected = new EntityBuilder(LoadTest::class);
        $expected->setSummary('Class LoadTest');
        $expected->setFileName(__FILE__);
        $expected->setProperty('property1', new PropertyMeta('property1', 'string[]', false,null,null,'property1', '', new ArrayBuilder(new ScalarTypeBuilder('string'))));
        $expected->setProperty('property2', new PropertyMeta('property2', 'string', true,'default0', 'email', '', '', new ScalarTypeBuilder('string')));
        $expected->setProperty('property3', new PropertyMeta('property3', null, true,1, null, '', ''));
        self::assertEquals($expected, $actual);
    }

    public function testBuildScalarType()
    {
        $loader = new EntityMetaLoader();
        $builder = $loader->loadFromClass(BuildScalarTypeTest::class);
        $actual = $builder->build(['property1'=>100]);
        $expected = new BuildScalarTypeTest();
        $expected->property1 = 100;
        self::assertEquals($expected, $actual);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>'not string']);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>102]);
        }, \InvalidArgumentException::class);
    }

    public function testBuildScalarTypeArray()
    {
        $loader = new EntityMetaLoader();
        $builder = $loader->loadFromClass(BuildScalarTypeArrayTest::class);
        $actual = $builder->build(['property1'=>[100]]);
        $expected = new BuildScalarTypeArrayTest();
        $expected->property1 = [100];
        self::assertEquals($expected, $actual);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>'not string']);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>[102]]);
        }, \InvalidArgumentException::class);
    }

    public function testBuildEntity()
    {
        $loader = new EntityMetaLoader();
        $builder = $loader->loadFromClass(BuildEntityTest::class);
        $actual = $builder->build([
            'property1'=>[
                'property1'=>100
            ]
        ]);

        $expected = new BuildEntityTest();
        $expected->property1 = new BuildScalarTypeTest();
        $expected->property1->property1 = 100;
        self::assertEquals($expected, $actual);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>'not string']);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build([
                'property1'=>[
                    'property1'=>102
                ]
            ]);
        }, \InvalidArgumentException::class);
    }

    public function testBuildEntityArray()
    {
        $loader = new EntityMetaLoader();
        $builder = $loader->loadFromClass(BuildEntityArrayTest::class);
        $actual = $builder->build([
            'property1'=>[
                ['property1'=>100]
            ]
        ]);

        $expected = new BuildEntityArrayTest();
        $property1 = new BuildScalarTypeTest();
        $property1->property1 = 100;
        $expected->property1 =[$property1];

        self::assertEquals($expected, $actual);

        self::assertException(function ()use($builder){
            $builder->build(['property1'=>'not string']);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build([
                'property1'=>[
                    ['property1'=>102]
                ]
            ]);
        }, \InvalidArgumentException::class);
    }
}