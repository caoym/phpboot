<?php

namespace PhpBoot\Tests;
use PhpBoot\Annotation\AnnotationBlock;
use PhpBoot\Annotation\AnnotationReader;
use PhpBoot\Annotation\AnnotationTag;

/**
 * class summary
 *
 * class description line1
 * 
 * @classAnn1 class Ann 1 {@childAnn1 child Ann 1} {@childAnn2 child Ann 2}
 * 
 * @classAnn2 class Ann 2
 */
class TestClass{

    /**
     * method summary
     *
     * method description
     *
     * @methodAnn1 method Ann 1
     */
    public function method1(){

    }

    /**
     * property1 summary
     * @propertyAnn1
     */
    public $property1;

    public $property2;
}

class AnnotationReaderTest extends TestCase
{
    public function testAll()
    {
        $actual = AnnotationReader::read(TestClass::class, $this->app->getCache());

        $expected = new AnnotationReader();
        $expected->class = new AnnotationBlock(
            TestClass::class,
            'class summary',
            "class description line1",
            [
                $tagP1 = new AnnotationTag(
                    'classAnn1',"class Ann 1 {@childAnn1 child Ann 1} {@childAnn2 child Ann 2}", [
                        $tagC1=new AnnotationTag('childAnn1', 'child Ann 1'),
                        $tagC2=new AnnotationTag('childAnn2', "child Ann 2")
                ]),
                $tagP2 = new AnnotationTag('classAnn2',"class Ann 2"),
            ]
            );
        $tagP1->parent = $tagP2->parent = $expected->class;
        $tagC2->parent = $tagC1->parent = $tagP1;

        $expected->methods = [
            'method1'=>new AnnotationBlock('method1','method summary', 'method description', [
                new AnnotationTag('methodAnn1','method Ann 1')
            ])
        ];
        $expected->methods['method1']->children[0]->parent = $expected->methods['method1'];

        $expected->properties = [
            'property1'=>new AnnotationBlock('property1', 'property1 summary', '', [
                new AnnotationTag('propertyAnn1')
            ]),
            'property2'=>new AnnotationBlock('property2'),
        ];

        $expected->properties['property1']->children[0]->parent = $expected->properties['property1'];

        self::assertEquals($expected, $actual);
    }
}