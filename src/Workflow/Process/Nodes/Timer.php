<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/2/23
 * Time: 下午9:36
 */

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;
use PhpBoot\Workflow\Process\Traits\SingleInput;
use PhpBoot\Workflow\Process\Traits\SingleOutput;


class Timer extends IntermediateEventNode
{

    public function __construct($nodeName, $second){
        $this->second = $second;
        parent::__construct($nodeName);
    }

    public function handle(ProcessContext $context, ProcessEngine $engine){
        count($this->outputs) or \PhpBoot\abort("no output connected from timer '{$this->getName()}'");
        $engine->delayTask($this->second, $this->outputs[0],'handleTimeout', $context);
    }
    public function handleTimeout(ProcessContext $context, ProcessEngine $engine){
        parent::handle($context, $engine);
    }

    use SingleInput;
    use SingleOutput;
    /**
     * @var int
     */
    private $second;
}