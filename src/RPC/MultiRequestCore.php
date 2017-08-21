<?php

namespace PhpBoot\RPC;

use PhpBoot\Exceptions\RpcException;

class MultiRequestCore
{
    /**
     * MultiRequest constructor.
     * @param callable[] $threads
     * @param callable $waitAll
     */
    public function __construct(array $threads, callable $waitAll)
    {
        foreach ($threads as $thread){
            $pos = count($this->threadResults);
            $this->threadResults[] = [null,null];
            $this->threads[] = function ()use($thread, $pos){
                try{
                    $this->threadResults[$pos][0] = $thread();
                }catch (\Exception $e){
                    $this->threadResults[$pos][1] = $e;
                }
            };
        }
        $this->waitAll = $waitAll;
    }

    public function run()
    {
        while ($thread = array_pop($this->threads)){
            $thread();
        };
    }

    public function wait($waitAble){
        array_push($this->waits, $waitAble);
        $this->run();

        if(count($this->waits)){
            $waitAll = $this->waitAll;
            $this->waitResults = $waitAll($this->waits);
            $this->waits = [];
        }

        $res =  array_pop($this->waitResults);
        if(isset($res[1])){
             \PhpBoot\abort(new RpcException($res['reason']));
        }else{
            return $res[0];
        }
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->threadResults;
    }

    /**
     * @var callable[]
     */
    protected $waits = [];

    /**
     * @var callable[]
     */
    protected $threads = [];

    protected $threadResults = [];
    /**
     * @var callable
     */
    protected $waitAll;

    /**
     * @var callable
     */
    protected $waitResults = [];
}