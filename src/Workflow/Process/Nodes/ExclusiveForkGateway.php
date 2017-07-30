<?php

namespace PhpBoot\Workflow\Process\Nodes;
use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;


/**
 * Class ExclusiveGateway
 * @package PhpBoot\Workflow
 * 排他网关(分发)
 *
 * 只选择第一个满足条件的链路输出, 若没有满足条件的输出, 抛出异常
 */
class ExclusiveForkGateway extends Gateway
{
    use SingleInput;

    public function addCondition(callable $cond, $comment='')
    {
        $next = new GatewayBranch($this, count($this->conditions));
        $this->conditions[] = [$cond, $next, $comment];
        return $next;
    }

    public function setDefault($comment=''){
        !$this->hasDefault() or \PhpBoot\abort(new  ProcessDefineException("default output of ExclusiveGateway {$this->getName()} has exist"));
        $next = new GatewayBranch($this, 'default');
        $this->default = [$next,$comment];
        return $next;
    }

    /**
     * @return bool
     */
    public function hasDefault(){
        return !!$this->default;
    }

    /**
     * @param ProcessContext $context
     * @param ProcessEngine $engine
     */
    public function dispatchNext(ProcessContext $context, ProcessEngine $engine)
    {
        //创建新令牌, 并为所有分支分配子令牌, 以便可以统一控制分支
        $context->setToken($engine->createToken($context->getToken()));

        foreach ($this->conditions as $output){
            list($elseif, $next, $comment) = $output;
            if($elseif == null || $elseif($context)){
                //创建子ProcessContext和子ProcessToken
                $branchContext = $engine->createContext($context);
                $branchContext->setToken(
                    $engine->createToken($context->getToken())
                );

                $next->handle($branchContext, $engine);
                return;
            }
        }
        if($this->default){
            list($next, $comment) = $this->default;

            $branchContext = $context;
            //创建子ProcessContext和子ProcessToken
            $branchContext = $engine->createContext($context);
            $branchContext->setToken(
                $engine->createToken($context->getToken())
            );
            $next->handle($branchContext, $engine);
            return;
        }

        \PhpBoot\abort(new ProcessDefineException("Error at ExclusiveGateway: {$this->getName()}, no sequence flow can be selected"));
    }

    /**
     * @var array [[$condition, $next, $comment], ... ]
     */
    private $conditions=[];

    /**
     * @var array [$next, $comment]
     */
    private $default;
}