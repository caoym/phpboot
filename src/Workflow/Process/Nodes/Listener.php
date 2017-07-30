<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;
use PhpBoot\Workflow\Process\Traits\SingleOutput;

class Listener extends IntermediateEventNode
{
    public function __construct($nodeName, $event){
        $this->event = $event;
        parent::__construct($nodeName);
    }

    public function handle(ProcessContext $context, ProcessEngine $engine){
        count($this->outputs) or \PhpBoot\abort("no output connected from Listener '{$this->getName()}'");
        $engine->catchEvent($this->event, $this->outputs[0]->getName(), 'handleEvent',$context);
    }
    public function handleEvent(ProcessContext $context, ProcessEngine $engine){
        parent::handle($context, $engine);
    }
    use SingleInput;
    use SingleOutput;
    /**
     * @var string
     */
    private $event;
}