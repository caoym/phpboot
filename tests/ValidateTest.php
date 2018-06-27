<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2018/6/27
 * Time: 下午1:07
 */

namespace PhpBoot\Tests;


use PhpBoot\Validator\Validator;

class ValidateTest extends TestCase
{
    public function testRuleIn()
    {
        $v = new Validator();
        $v->rule('in:1,2,3', 'a');
        $res = $v->withData(['a'=>'0'])->validate();
        self::assertFalse($res);

        $res = $v->withData(['a'=>1])->validate();
        self::assertTrue($res);

    }

    public function testRuleMinMax()
    {
        $v = new Validator();
        $v->rule('min:1|max:3', 'a');
        $res = $v->withData(['a'=>'1'])->validate();
        self::assertTrue($res);

        $res = $v->withData(['a'=>1])->validate();
        self::assertTrue($res);
        $res = $v->withData(['a'=>3])->validate();
        self::assertTrue($res);
        $res = $v->withData(['a'=>'3'])->validate();
        self::assertTrue($res);

        $res = $v->withData(['a'=>'0'])->validate();
        self::assertFalse($res);

        $res = $v->withData(['a'=>'4'])->validate();
        self::assertFalse($res);


    }

}