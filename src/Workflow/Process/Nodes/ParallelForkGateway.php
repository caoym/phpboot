<?php

namespace PhpBoot\Workflow\Process\Nodes;
use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;


/**
 * Class ParallelForkGateway
 * @package PhpBoot\Workflow
 * 并行网关(分发)
 *
 * 并行触发所有输出
 */
class ParallelForkGateway extends Gateway
{
    use SingleInput;

    /**
     * @param ProcessContext $context
     * @param ProcessEngine $engine
     */
    public function dispatchNext(ProcessContext $context, ProcessEngine $engine)
    {
        $context->setToken($engine->createToken($context->getToken()));

        //创建新令牌, 并为所有分支分配子令牌, 以便可以统一控制分支
        foreach ($this->getOutputs() as $output){
            //创建子ProcessContext和子ProcessToken
            $branchContext = $engine->createContext($context);
            $branchContext->setToken(
                $engine->createToken($context->getToken())
            );
            $engine->pushTask($output->getName(), 'handle', $branchContext);
        }
        \PhpBoot\abort(new ProcessDefineException("Error at ".__CLASS__.": {$this->getName()}, no sequence flow can be selected"));
    }
}