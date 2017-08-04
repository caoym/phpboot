<?php

namespace PhpBoot\RPC;

use GuzzleHttp\Promise;

use PhpBoot\Exceptions\RpcException;

class MultiRpc
{
    public static function run(array $threads)
    {
        /**
         * 返回以下形式接口
         *
         * [
         *      [成功值1, null],
         *      [成功值2, null],
         *      [null,  失败异常1],
         *      ...
         * ]
         *
         */
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