<?php

namespace PhpBoot\Workflow\Process\Nodes;
use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleOutput;


/**
 * Class ParallelJoinGateway
 * @package PhpBoot\Workflow
 * 并行网关(合并)
 *
 * 等待所有满足条件的输入到达, 触发输出
 */
class ParallelJoinGateway extends Gateway
{
    use SingleOutput;

    public function handle(ProcessContext $context, ProcessEngine $engine)
    {
        //合并输入时, 重置token为流程分支前的token
        $inputs = $engine->getNodeStack($this->getName());
        $inputs += [$context];

        $childTokens = $context->getToken()->getParent()->getChildren();
        $childTokenNames = [];
        foreach ($childTokens as $childToken){
            $childTokenNames[$childToken->getName()] = 1;
        }

        $contexts = [];
        //判断是否所有token均已达到
        foreach ($inputs as $input){
            $name = $input->getToken()->getName();
            if(array_key_exists($name,$childTokenNames)){
                $childTokenNames[$name] = 0;
                $contexts[] =  $input;
            }
        }
        if(array_sum($childTokenNames) == 0){ //合并
            $merged = $engine->mergeContexts($contexts);
            $merged->setToken(
                $context->getToken()->getParent()->getParent()
            );
            parent::handle($context, $engine);
        }else{
            $engine->pushNodeStack($this->getName(), $context);
        }

    }
}
