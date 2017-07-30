<?php

namespace PhpBoot\Workflow\Builder;
use PhpBoot\Workflow\Process\Nodes\BeginEvent;
use PhpBoot\Workflow\Process\Nodes\ConnectedAble;
use PhpBoot\Workflow\Process\Nodes\EndEvent;
use PhpBoot\Workflow\Process\Nodes\EventBasedGateway;
use PhpBoot\Workflow\Process\Nodes\ExclusiveForkGateway;
use PhpBoot\Workflow\Process\Nodes\ExclusiveJoinGateway;
use PhpBoot\Workflow\Process\Nodes\Gateway;
use PhpBoot\Workflow\Process\Nodes\InclusiveForkGateway;
use PhpBoot\Workflow\Process\Nodes\InclusiveJoinGateway;
use PhpBoot\Workflow\Process\Nodes\Listener;
use PhpBoot\Workflow\Process\Nodes\ParallelForkGateway;
use PhpBoot\Workflow\Process\Nodes\ParallelJoinGateway;
use PhpBoot\Workflow\Process\Nodes\Timer;
use PhpBoot\Workflow\Process\Process;

/**
 * Class GraphMaker
 * @package PhpBoot\Workflow\Builder
 *
 */
class GraphMaker
{
    /**
     * GraphMaker constructor.
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
        $this->make();
    }

    public function __toString()
    {
        return $this->graphText;
    }

    /**
     * Make a mermaid flowcharts text from Process.
     * The text can be rendered to an image by http://knsv.github.io/mermaid/live_editor/
     *
     * Possible directions are:
     *  TB - top bottom
     *  BT - bottom top
     *  RL - right left
     *  LR - left right

     *  TD - same as TB
     *
     * @param Process $process
     * @return string
     */
    private function make($directions = 'LR')
    {
        $graph = "graph {$directions}\n";
        $notes = $this->process->getNodes();

        foreach ($notes as $v){
            $def = '';
            if($v instanceof BeginEvent || $v instanceof EndEvent){
                $def = "(({$v->getName()}))";
            }else if($v instanceof EventBasedGateway){
                $def = "{fa:fa-empire}";
            }else if($v instanceof ExclusiveForkGateway ||
                $v instanceof ExclusiveJoinGateway){
                $def = "{X}";
            }else if($v instanceof InclusiveForkGateway ||
                $v instanceof InclusiveJoinGateway){
                $def = "{O}";
            }else if($v instanceof ParallelForkGateway ||
                $v instanceof ParallelJoinGateway){
                $def = "{+}";
            }else if($v instanceof Timer){
                $def = "((fa:fa-clock-o))";
            }else if($v instanceof Listener){
                $def = "((fa:fa-envelope-o))";
            }else{
                $def = "[{$v->getName()}]";
            }
            $graph .= "    {$v->getName()}_0$def\n";
            $this->getConnections($v);
        }

        $isCreated = [];
        foreach ($this->connections as $con){
            list($from, $to) = $con;
            $graph .= "    {$from}_0-->{$to}_0\n";
        }
        $this->graphText = $graph;
    }


    /**
     * @param ConnectedAble $root
     * @param string[][] $connections
     * @return  void
     */
    private function getConnections(ConnectedAble $root)
    {
        foreach ($root->getOutputs() as $output){
            if($this->addConnection($root->getName(), $output->getName())){
                $this->getConnections($output);
            }
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool
     */
    private function addConnection($from, $to){
        if(in_array([$from, $to], $this->connections)){
            return false;
        }
        $this->connections[] = [$from, $to];
        return true;
    }

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string[][]
     */
    private $connections=[];

    private $graphText;
}