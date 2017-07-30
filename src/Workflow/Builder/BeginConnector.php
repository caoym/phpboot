<?php

namespace PhpBoot\Workflow\Builder;

use PhpBoot\Workflow\Process\Nodes\BeginEvent;
use PhpBoot\Workflow\Process\Process;

class BeginConnector extends Connector
{
    function __construct(Process $process, $currentNode)
    {
        if(!$process->hasNode($currentNode)){
            $process->addNodeInstance($currentNode, new BeginEvent($currentNode));
        }
        parent::__construct($process, $currentNode);
    }
}