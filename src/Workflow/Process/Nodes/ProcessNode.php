<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Utils\SerializableFunc;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;

class ProcessNode implements ConnectedAble
{
    /**
     * ProcessTaskContainer constructor.
     * @param string $nodeName node name
     * @param string $nodeClass class name of the node
     */
    public function __construct($nodeName){
        $this->nodeName = $nodeName;
    }

    public function preConnect(ConnectedAble $from){

    }

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

    /**
     * @param SerializableFunc $hook
     * function(ProcessContext $context, ProcessEngine $engine, ProcessTaskContainer $node, callable $next)
     *
     */
    public function setHook(SerializableFunc $hook){
        $this->hook = $hook;
    }
    public function getHook(){
        return $this->hook;
    }

    public function connectTo(ConnectedAble $next){
        $next->preConnect($this);
        $this->outputs[] = $next;
        $next->postConnect($this);
    }

    public function handle(ProcessContext $context, ProcessEngine $engine){
        if($this->hook){
            $hook = $this->hook;
            $hook($this, $context, $engine, function()use($context, $engine){
                $this->handleImpl($context, $engine);
            });
        }else{
            $this->handleImpl($context, $engine);
        }
    }
    private function handleImpl(ProcessContext $context, ProcessEngine $engine){
        // init exceptionTo handler
        $this->dispatchInternal($context, $engine);
        $this->dispatchNext($context, $engine);

    }

    protected function dispatchInternal(ProcessContext $context, ProcessEngine $engine){

    }

    protected function dispatchNext(ProcessContext $context, ProcessEngine $engine){
        //call next nodes
        foreach ($this->outputs as $nextNode){
            $engine->pushTask($nextNode->getName(), 'handle', $context);
        }
    }
    /**
     * @return string
     */
    public function getName(){
        return $this->nodeName;
    }
    /**
     * @param string $nodeName
     */
    public function setName($nodeName)
    {
        $this->nodeName = $nodeName;
    }
    /**
     * from nodes
     * @var ConnectedAble[]
     */
    protected $inputs = [];
    /**
     * @var ConnectedAble[]
     */
    protected $outputs=[];

    private $nodeName;
    /**
     * @var callable
     */
    private $hook = null;
}