<?php

namespace  PhpBoot\Tests;

use PhpBoot\Utils\AnnotationParams;

class AnnotationParamsTest extends TestCase
{
    public function testSplit()
    {
        $testStr = ' a b"bb ccc   \"  c"c  ddd "e e ';
        $params = new AnnotationParams($testStr, 0);
        self::assertEquals($params->count(), 0);

        $params = new AnnotationParams($testStr, 1);
        self::assertEquals($params->count(), 1);
        self::assertEquals($params->getRawParam(0), $testStr);
        self::assertEquals($params->getRawParam(0), $testStr);
        self::assertNull($params->getRawParam(1));

        $params = new AnnotationParams($testStr, 2);
        self::assertEquals($params->count(), 2);
        self::assertEquals($params->getRawParam(0), 'a');
        self::assertEquals($params->getRawParam(1), 'b"bb ccc   \"  c"c  ddd "e e ');
        self::assertNull($params->getRawParam(2));

        $params = new AnnotationParams($testStr, 3);
        self::assertEquals($params->count(), 3);
        self::assertEquals($params->getRawParam(0), 'a');
        self::assertEquals($params->getRawParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getRawParam(2), 'ddd "e e ');
        self::assertNull($params->getRawParam(3));

        $params = new AnnotationParams($testStr, 4);
        self::assertEquals($params->count(), 4);
        self::assertEquals($params->getRawParam(0), 'a');
        self::assertEquals($params->getRawParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getRawParam(2), 'ddd');
        self::assertEquals($params->getRawParam(3), '"e e ');
        self::assertNull($params->getRawParam(4));

        $params = new AnnotationParams($testStr, 5);
        self::assertEquals($params->count(), 4);
        self::assertEquals($params->getRawParam(0), 'a');
        self::assertEquals($params->getRawParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getRawParam(2), 'ddd');
        self::assertEquals($params->getRawParam(3), '"e e ');
        self::assertNull($params->getRawParam(4));
    }

    public function testStripSlashes()
    {
        $testStr = 'abc';
        $params = new AnnotationParams($testStr, 1);
        self::assertEquals($params->getParam(0), 'abc');

        $testStr = '"abc\""';
        $params = new AnnotationParams($testStr, 1);
        self::assertEquals($params->getParam(0), 'abc"');

        $testStr = '"abc\"';
        $params = new AnnotationParams($testStr, 1);
        self::assertEquals($params->getParam(0, null, true), '"abc\"');

        $testStr = '"abc\"';
        $params = new AnnotationParams($testStr, 1);
        self::assertException(function()use($params){
            $params->getParam(0, null, false);
        });


    }

}