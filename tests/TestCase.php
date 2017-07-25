<?php

namespace PhpBoot\Tests;


class TestCase extends \PHPUnit_Framework_TestCase
{
    protected static function assertException(callable  $fun, $expectedClass = null, $expectedMessage = null){
        $throw = false;
        try{
            $fun();
        }catch (\Exception $e){
            $throw = true;
            if($expectedClass){
                self::assertInstanceOf($expectedClass, $e);
            }
            if($expectedMessage !== null){
                self::assertEquals($expectedMessage, $e->getMessage());
            }
        }
        self::assertTrue($throw);
    }
}