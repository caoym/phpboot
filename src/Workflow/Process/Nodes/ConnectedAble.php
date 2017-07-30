<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Process\ProcessContext;
use PhpBoot\Workflow\Process\ProcessEngine;

interface ConnectedAble
{

    /**
     * @param ConnectedAble $from
     * @return void
     */
    public function preConnect(ConnectedAble $from);

    /**
     * @param ConnectedAble $from
     * @return void
     */
    public function postConnect(ConnectedAble $from);

    /**
     * @param ConnectedAble $next
     * @return void
     */
    public function connectTo(ConnectedAble $next);

    /**
     * @param ProcessContext $context
     * @param ProcessEngine $engine
     * @return void
     */
    public function handle(ProcessContext $context, ProcessEngine $engine);
    /**
     * @return ConnectedAble[]
     */
    public function getInputs();

    /**
     * @return ConnectedAble[]
     */
    public function getOutputs();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);
}