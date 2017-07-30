<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;


class EventBasedGateway extends Gateway
{
    public function connectTo(ConnectedAble $next){
        //只允许连接事件节点
        $next instanceof IntermediateEventNode or \PhpBoot\abort(
            new ProcessDefineException(
                "EventBasedGateway {$this->getName()} should always connect to IntermediateEventNode"
            )
        );
        parent::connectTo($next);

        //set hook
        $next->setHook(new SerializableFunc([$this, 'hookHandle']));

    }

    public function hookHandle(ProcessContext $context,
                                   ProcessEngine $engine,
                                   ProcessTaskContainer $hookedNode,
                                   callable $next
                                    ){
        //一旦一个事件触发, 关闭其他链路上的token
        $token = $context->getToken()->getParent();
        foreach ($token->getChildren() as $child){
            if($child !== $context->getToken()){
                $child->disable();
            }
        }
        $next();
    }

    public function dispatchNext(ProcessContext $context, ProcessEngine $engine)
    {
        //call next nodes
        $token = $engine->createToken($context->getToken());
        $context->setToken($token);
        foreach ($this->outputs as $output){
            $newContext = $engine->createContext($context);
            $newContext->setToken($engine->createToken($token));
            $engine->pushTask($output->getName(), 'handle', $newContext);
        }
    }

    use SingleInput;
}