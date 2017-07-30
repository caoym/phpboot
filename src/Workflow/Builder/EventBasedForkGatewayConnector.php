<?php

namespace PhpBoot\Workflow\Builder;

use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\Nodes\ConnectedAble;
use PhpBoot\Workflow\Process\Nodes\ExclusiveForkGateway;
use PhpBoot\Workflow\Process\Nodes\InclusiveForkGateway;
use PhpBoot\Workflow\Process\Nodes\Timer;
use PhpBoot\Workflow\Process\Process;

/**
 * Class EventBasedForkGatewayConnector
 * @package PhpBoot\Workflow\Builder
 */
class EventBasedForkGatewayConnector
{
    public  function __construct(Process $process, ConnectedAble $currentNode)
    {
        $this->process = $process;
        $this->currentNode = $currentNode;
    }

    /**
     * @param $name
     * @param null $second
     * @param string $comment
     * @return Connector
     */
    public function timer($name, $second=null, $comment='')
    {
        $node = null;
        if($this->process->hasNode($name)){
            $node = $this->process->getNode($name);
        }else{
            $second !== null or \PhpBoot\abort(new ProcessDefineException("param 'second' of timer $name not set"));
            $node = $this->process->addNodeInstance($name, new Timer($name, $second));
            $this->process->connect($this->currentNode, $node);
        }
        $this->currentNode = $node;
        return new Connector($this->process, $node);
    }

    /**
     * @param $name
     * @param null $event
     * @param string $comment
     * @return Connector
     */
    public function listener($name, $event=null, $comment='')
    {
        $node = null;
        if($this->process->hasNode($name)){
            $node = $this->process->getNode($name);
        }else{
            $event !== null or \PhpBoot\abort(new ProcessDefineException("param 'event' of listener $name not set"));
            $node = $this->process->addNodeInstance($name, new Listener($name, $event));
            $this->process->connect($this->currentNode, $node);
        }
        return new Connector($this->process, $node);
    }

    /**
     * @var Process
     */
    private $process;
    /**
     * @var ConnectedAble
     */
    private $currentNode;
}