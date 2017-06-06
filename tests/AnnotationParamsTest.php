<?php

namespace  PhpBoot\Tests;

use PhpBoot\Utils\AnnotationParams;

class AnnotationParamsTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $testStr = ' a b"bb ccc   \"  c"c  ddd "e e ';
//        $params = new AnnotationParams($testStr, 1);
//        self::assertEquals($params->count(), 1);
//        self::assertEquals($params->getParam(0), $testStr);
//        self::assertEquals($params->getRawParam(0), $testStr);
//        self::assertNull($params->getParam(1));
//
//        $params = new AnnotationParams($testStr, 2);
//        self::assertEquals($params->count(), 2);
//        self::assertEquals($params->getParam(0), 'a');
//        self::assertEquals($params->getParam(1), 'b"bb ccc   \"  c"c  ddd "e e ');
//        //self::assertEquals($params->getRawParam(0), 'a');
//        //self::assertEquals($params->getRawParam(1), 'b"bb ccc   "  cc  ddd e e ');
//        self::assertNull($params->getParam(2));

        $params = new AnnotationParams($testStr, 3);
        self::assertEquals($params->count(), 3);
        self::assertEquals($params->getParam(0), 'a');
        self::assertEquals($params->getParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getParam(2), 'ddd "e e ');

        //self::assertEquals($params->getRawParam(0), 'a');
        //self::assertEquals($params->getRawParam(1), 'b"bb ccc   "  cc  ddd e e ');
        self::assertNull($params->getParam(3));

        $params = new AnnotationParams($testStr, 4);
        self::assertEquals($params->count(), 4);
        self::assertEquals($params->getParam(0), 'a');
        self::assertEquals($params->getParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getParam(2), 'ddd');
        self::assertEquals($params->getParam(3), '"e e ');

        //self::assertEquals($params->getRawParam(0), 'a');
        //self::assertEquals($params->getRawParam(1), 'b"bb ccc   "  cc  ddd e e ');
        self::assertNull($params->getParam(4));

        $params = new AnnotationParams($testStr, 5);
        self::assertEquals($params->count(), 4);
        self::assertEquals($params->getParam(0), 'a');
        self::assertEquals($params->getParam(1), 'b"bb ccc   \"  c"c');
        self::assertEquals($params->getParam(2), 'ddd');
        self::assertEquals($params->getParam(3), '"e e ');

        //self::assertEquals($params->getRawParam(0), 'a');
        //self::assertEquals($params->getRawParam(1), 'b"bb ccc   "  cc  ddd e e ');
        self::assertNull($params->getParam(4));

    }
}