<?php

namespace PhpBoot\Workflow\Builder;

use PhpBoot\Workflow\Process\Nodes\ExclusiveForkGateway;
use PhpBoot\Workflow\Process\Nodes\InclusiveForkGateway;
use PhpBoot\Workflow\Process\Process;

/**
 * Class InclusiveJoinGatewayConnector
 * @package PhpBoot\Workflow\Builder
 */
class InclusiveForkGatewayConnector
{
    public  function __construct(Process $process, InclusiveForkGateway $gateway )
    {
        $this->gateway = $gateway;
        $this->process = $process;
    }

    /**
     * @param callable $condition
     * @param string $comment
     * @return Connector
     */
    public function when(callable $condition, $comment=''){
        $cond = $this->gateway->addCondition($condition, $comment);
        return new Connector($this->process, $cond);
    }

    /**
     * @var InclusiveForkGateway
     */
    private $gateway;
    /**
     * @var Process
     */
    private $process;
}