<?php

namespace PhpBoot\Tests;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationReader;

/**
 * class summary
 * class description
 * 
 * @classAnn1 class Ann 1 @childAnn1 child Ann 1 @childAnn2 
 * child Ann 2
 * 
 * @classAnn2 class Ann 2
 */
class TestClass{

    /**
     * method summary
     * method description
     * 
     * @methodAnn1 method Ann 1
     * method Ann 1
     * 
     * @methodAnn2 method Ann 2
     */
    public function method1(){

    }

    /**
     * @propertyAnn1
     */
    public $property1;

    public $property2;
}

class AnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $actual = AnnotationReader::read(TestClass::class);
        $expected = new AnnotationReader();
        $expected->class = new AnnotationBlock();

        self::assertEquals($expected, $actual);
    }
}