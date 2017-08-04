<?php

namespace PhpBoot\RPC;

use GuzzleHttp\Promise;

use PhpBoot\Exceptions\RpcException;

class MultiRpc
{
    public static function run(array $threads)
    {
        return MultiRequest::run($threads, function($promises){
            $res = [];
            foreach (Promise\settle($promises)->wait() as $i){
                if(isset($i['reason'])){
                    $res[] = [null, new RpcException($i['reason'])];
                }else{
                    $res[] = [$i['value'], null];
                }
            }
            return $res;
        });
    }

    public static function isRunning(){
        return MultiRequest::isRunning();
    }

    public static function wait(Promise\Promise $waitAble)
    {
        return MultiRequest::wait($waitAble);
    }

}