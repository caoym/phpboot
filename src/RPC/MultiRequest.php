<?php

namespace PhpBoot\RPC;

/**
 * 并发执行多个异步请求
 *
 */
class MultiRequest
{
    public static function run(array $threads, callable $waitAll)
    {
        $request = new MultiRequestCore($threads, $waitAll);
        $id = spl_object_hash($request);
        self::$contexts[$id] = $request;
        $oriId = self::$currentContext;
        self::$currentContext = $id;
        $request->run();
        self::$currentContext = $oriId;
        return $request->getResults();
    }

    public static function isRunning(){
        return !!self::$currentContext;
    }

    public static function wait($waitAble)
    {
        self::isRunning() or \PhpBoot\abort("can not call wait() out of MultiRequest::run");
        $request = self::$contexts[self::$currentContext];
        return $request->wait($waitAble);
    }

    /**
     * @var MultiRequestCore[]
     */
    protected static $contexts;
    protected static $currentContext;


}