<?php

namespace PhpBoot\Tests;


use PhpBoot\Entity\ScalarTypeBuilder;

class ScalarTypeBuilderTest extends TestCase
{
    public function testCastToInt()
    {
        $builder = new ScalarTypeBuilder('int');
        self::assertException(function ()use($builder){
            $builder->build('not int', true);
        }, \InvalidArgumentException::class);

        self::assertEquals($builder->build('not int', false), 0);

        self::assertEquals($builder->build('123', true), 123);

        self::assertEquals($builder->build('000', true), 0);

        self::assertEquals($builder->build(null, true), 0);

        self::assertEquals($builder->build(true, true), 1);

        self::assertEquals($builder->build(false, true), 0);
    }

    public function testCastToBool()
    {
        $builder = new ScalarTypeBuilder('bool');
        self::assertException(function ()use($builder){
            $builder->build('not bool', true);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build('true', true);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($builder){
            $builder->build('false', true);
        }, \InvalidArgumentException::class);

        self::assertEquals($builder->build(1, true), true);
        self::assertEquals($builder->build('1', true), true);
        self::assertEquals($builder->build('0', true), false);
    }

    public function testCastToString()
    {
        $builder = new ScalarTypeBuilder('string');
        self::assertException(function ()use($builder){
            $builder->build([1], true);
        }, \InvalidArgumentException::class);

        $builder = new ScalarTypeBuilder('string');
        self::assertException(function ()use($builder){
            $builder->build(new \stdClass(), true);
        }, \InvalidArgumentException::class);

        self::assertEquals($builder->build(1, true), '1');
        self::assertEquals($builder->build(1.9, true), '1.9');
        self::assertEquals($builder->build(0, true), '0');
        self::assertEquals($builder->build(true, true), '1');
        self::assertEquals($builder->build(false, true), '0');
    }

}