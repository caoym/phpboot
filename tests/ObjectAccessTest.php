<?php
namespace  PhpBoot\Tests;

use PhpBoot\Utils\ObjectAccess;

class GetMethodTest{
    public function getB(){
        return 1;
    }
}
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/11/13
 * Time: 下午3:13
 */
class ObjectAccessTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayAccess(){

        $arr = [
            'a'=>[
                'b'=>1
            ]
        ];
        $acc = new ObjectAccess($arr);

        $this->assertSame(
            $acc->has('$'),
            true
        );
        $this->assertSame(
            $acc->get('$'),
            $arr
        );

        $this->assertSame(
            $acc->has('$.a.b'),
            true
        );

        $this->assertSame(
            $acc->get('$.a.b'),
            1
        );

    }

    public function testObjectAccess(){

        $arr = new \stdClass();
        $arr->a = new \stdClass();
        $arr->a->b = 1;

        $acc = new ObjectAccess($arr);

        $this->assertSame(
            $acc->has('$'),
            true
        );
        $this->assertSame(
            $acc->get('$'),
            $arr
        );

        $this->assertSame(
            $acc->has('$.a.b'),
            true
        );

        $this->assertSame(
            $acc->get('$.a.b'),
            1
        );

    }

    public function testObjectGetMethodAccess(){
        $arr = new GetMethodTest();
        $acc = new ObjectAccess($arr);
        $this->assertSame(
            $acc->has('$'),
            true
        );
        $this->assertSame(
            $acc->get('$'),
            $arr
        );

        $this->assertSame(
            $acc->has('$.b'),
            true
        );

        $this->assertSame(
            $acc->get('$.b'),
            1
        );

    }

    public function testGetHookAccess(){
        $arr = [
            'a'=>[
                'b'=>1
            ]
        ];
        $acc = new ObjectAccess($arr,[
            'a'=>[
                'b'=>function(){return 2;}
            ]
        ]);

        $this->assertSame(
            $acc->has('$'),
            true
        );
        $this->assertSame(
            $acc->get('$'),
            $arr
        );

        $this->assertSame(
            $acc->has('$.a.b'),
            true
        );

        $this->assertSame(
            $acc->get('$.a.b'),
            2
        );

    }

    public function testSetArray(){
        $data = [];
        $acc = new ObjectAccess($data);
        $this->assertSame($acc->set('$.a.b', 1), true);
        $this->assertSame($acc->set('$.a.b.c', 1), false);
        $this->assertSame($data, ['a'=>['b'=>1]]);
    }


}