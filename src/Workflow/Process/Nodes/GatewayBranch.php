<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\NoInput;


class GatewayBranch implements ConnectedAble
{
    public function __construct(Gateway $gateway, $name)
    {
        $this->gateway = $gateway;
        $this->name = $gateway->getName().'.'.$name;

    }

    use NoInput;

    public function connectTo(ConnectedAble $next)
    {
        !$this->next or  \PhpBoot\abort(new ProcessDefineException("connect {$this->getName()} to {$next->getName()} failed, an ".get_class($this)." can only have ONE output"));
        $this->next = $next;
        $this->gateway->connectTo($next);
    }

    public function handle(ProcessContext $context, ProcessEngine $engine)
    {
        $this->next or \PhpBoot\abort("no output connected at {$this->getName()}");
        $engine->pushTask($this->next->getName(),'handle', $context);
    }

    public function getInputs()
    {
       return [];
    }

    public function getOutputs()
    {
        return $this->next?[$this->next]:[];
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var ConnectedAble
     */
    private $next;
    private $name;


}