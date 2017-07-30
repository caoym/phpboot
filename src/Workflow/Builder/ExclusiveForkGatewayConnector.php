<?php

namespace PhpBoot\Workflow\Builder;

use PhpBoot\Workflow\Process\Nodes\ExclusiveForkGateway;
use PhpBoot\Workflow\Process\Process;

/**
 * Class ExclusiveForkGatewayConnector
 * @package PhpBoot\Workflow\Builder
 */
class ExclusiveForkGatewayConnector
{
    public  function __construct(Process $process, ExclusiveForkGateway $exclusiveGateway )
    {
        $this->exclusiveGateway = $exclusiveGateway;
        $this->process = $process;
    }

    /**
     * @param callable $condition
     * @param string $comment
     * @return Connector
     */
    public function when(callable $condition, $comment=''){
        $cond = $this->exclusiveGateway->addCondition($condition, $comment);
        return new Connector($this->process, $cond);
    }
    /**
     * @param string $comment
     * @return Connector
     */
    public function otherwise($comment=''){
        $cond = $this->exclusiveGateway->setDefault($comment);
        return new Connector($this->process, $cond);
    }

    /**
     * @var ExclusiveForkGateway
     */
    private $exclusiveGateway;
    /**
     * @var Process
     */
    private $process;
}