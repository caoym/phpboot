<?php

namespace PhpBoot\Tests;


use PhpBoot\Utils\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testSet()
    {
        $test = [];
        ArrayHelper::set($test, 'a.b.c', 1);
        self::assertEquals(['a'=>['b'=>['c'=>1]]], $test);

        ArrayHelper::set($test, 'a.b.c', 2);
        self::assertEquals(['a'=>['b'=>['c'=>2]]], $test);

        self::assertException(function()use($test){
            ArrayHelper::set($test, 'a.b.c.d', 1);
        });

        ArrayHelper::set($test, 'a.b.d', 3);
        self::assertEquals(['a'=>['b'=>['c'=>2, 'd'=>3]]], $test);

    }
}