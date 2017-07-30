<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;
use PhpBoot\Workflow\Process\Traits\SingleOutput;


class ProcessTaskContainer extends ProcessNode
{
    /**
     * ProcessTaskContainer constructor.
     * @param string $nodeName node name
     * @param string $taskClass class name of the task
     */
    public function __construct($nodeName, $taskClass){
        $reflection = new \ReflectionClass($taskClass);
        $reflection->isSubclassOf(ProcessTaskInterface::class) or \PhpBoot\abort(new ProcessDefineException("$taskClass not a  ProcessTaskInterface"));
        $this->taskClass = $taskClass;
        parent::__construct($nodeName);
    }

    use SingleInput;
    use SingleOutput;

    public function postConnect(ConnectedAble $from){
        $this->inputs[] = $from;
    }

    /**
     * @return ConnectedAble[]
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @return ConnectedAble[]
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    public function exceptionTo($exception, ProcessTaskContainer $next){
        $next->preConnect($this);
        $this->exceptionTo[] = [$exception, $next];
        $next->postConnect($this);
    }

    public function dispatchInternal(ProcessContext $context, ProcessEngine $engine){
        // init exceptionTo handler
        try{
            //call
            $node = new $this->taskClass;
            $node->handle($context);
        }catch (\Exception $e){
            $context->setLastException($this->name, $e);
            $handled = false;
            foreach ($this->exceptionTo as $to){
                list($key, $nextNode) = $to;
                if ($e instanceof $key){
                    $handled = true;
                    $engine->pushTask($nextNode->getName(), 'handle', $context);
                }
            }
            if(!$handled){
                throw $e;
            }else{
                return;
            }
        }
    }
    public function getClass(){
        return $this->taskClass;
    }

    /**
     * @var ConnectedAble[]
     */
    protected $exceptionTo;

    private $taskClass;


}