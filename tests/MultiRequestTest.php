<?php

namespace PhpBoot\Tests;


use PhpBoot\RPC\MultiRequest;

class MultiRequestTest extends TestCase
{
    public function testMultiWithoutWait()
    {
        $res = MultiRequest::run(
            [
                function(){
                    return 1;
                },
                function(){
                    return 2;
                },
                function(){
                    return 3;
                }
            ],
            function($waits){
                $res = [];
                foreach ($waits as $v){
                    $res[] = [$v, null];
                }
                return $res;
            }
        );
        self::assertEquals([[1, null], [2, null], [3, null]],$res);
    }

    public function testMultiRequestWait()
    {
        $res = MultiRequest::run(
            [
                function(){
                    return MultiRequest::wait(1);
                },
                function(){
                    return MultiRequest::wait(2);
                },
                function(){
                    return MultiRequest::wait(3);
                }
            ],
            function($waits){
                $res = [];
                foreach ($waits as $v){
                    $res[] = [$v, null];
                }
                return $res;
            }
        );
        self::assertEquals([[1, null],[2, null],[3, null]], $res);
    }

    public function testMultiRequestWithException()
    {
        $res = MultiRequest::run(
            [
                function(){
                    return 1;
                },
                function(){
                    throw new \Exception('2');
                },
                function(){
                    return MultiRequest::wait(3);
                },
                function(){
                    MultiRequest::wait(4);
                    throw new \Exception('e4');
                },
                function(){
                    return 5;
                }
            ],
            function($waits){
                $res = [];
                foreach ($waits as $v){
                    $res[] = [$v, null];
                }
                return $res;
            }
        );
        self::assertEquals([1, null],$res[0]);
        self::assertEquals('2', $res[1][1]->getMessage());
        self::assertEquals([3, null],$res[2]);
        self::assertEquals('e4', $res[3][1]->getMessage());
        self::assertEquals([5, null], $res[4]);
    }
}