<?php

namespace PhpBoot\Tests;


use PhpBoot\Entity\ScalarTypeContainer;

class ScalarTypeContainerTest extends TestCase
{
    public function testCastToInt()
    {
        $container = new ScalarTypeContainer('int');
        self::assertException(function ()use($container){
            $container->make('not int', true);
        }, \InvalidArgumentException::class);

        self::assertEquals($container->make('not int', false), 0);

        self::assertEquals($container->make('123', true), 123);

        self::assertEquals($container->make('000', true), 0);

        self::assertEquals($container->make(null, true), 0);

        self::assertEquals($container->make(true, true), 1);

        self::assertEquals($container->make(false, true), 0);
    }

    public function testCastToBool()
    {
        $container = new ScalarTypeContainer('bool');
        self::assertException(function ()use($container){
            $container->make('not bool', true);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($container){
            $container->make('true', true);
        }, \InvalidArgumentException::class);

        self::assertException(function ()use($container){
            $container->make('false', true);
        }, \InvalidArgumentException::class);

        self::assertEquals($container->make(1, true), true);
        self::assertEquals($container->make('1', true), true);
        self::assertEquals($container->make('0', true), false);
    }

    public function testCastToString()
    {
        $container = new ScalarTypeContainer('string');
        self::assertException(function ()use($container){
            $container->make([1], true);
        }, \InvalidArgumentException::class);

        $container = new ScalarTypeContainer('string');
        self::assertException(function ()use($container){
            $container->make(new \stdClass(), true);
        }, \InvalidArgumentException::class);

        self::assertEquals($container->make(1, true), '1');
        self::assertEquals($container->make(1.9, true), '1.9');
        self::assertEquals($container->make(0, true), '0');
        self::assertEquals($container->make(true, true), '1');
        self::assertEquals($container->make(false, true), '0');
    }

}