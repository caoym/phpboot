<?php
namespace PhpBoot\Workflow\Process;
use PhpBoot\Workflow\Exceptions\ProcessDefineException;
use PhpBoot\Workflow\Process\Nodes\BeginEvent;
use PhpBoot\Workflow\Process\Nodes\ConnectedAble;
use PhpBoot\Workflow\Process\Nodes\ProcessTaskContainer;
use PhpBoot\Workflow\Utils\SerializableFunc;
use PhpBoot\Workflow\Utils\Verify;

/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/2/21
 * Time: 下午11:18
 */
class Process
{

    public function addNodeInstance($name, ConnectedAble $node){
        !array_key_exists($name, $this->nodes)
            or \PhpBoot\abort(new ProcessDefineException("node $name already exist"));
        $node->setName($name);
        $this->nodes[$name] = $node;
        return $node;
    }

    public function connect($from, $to){
        if(is_string($from)){
            $from = $this->getNode($from);
        }
        if(is_string($to)){
            $to = $this->getNode($to);
        }
        $from->connectTo($to);
    }

    public function getNode($name){
        array_key_exists($name, $this->nodes)
            or \PhpBoot\abort(new ProcessDefineException("node $name not found"));
        return $this->nodes[$name];
    }
    public function hasNode($name){
        return array_key_exists($name, $this->nodes);
    }

    /**
     * @return Nodes\ConnectedAble[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }
    /**
     * @var ConnectedAble[]
     */
    private $nodes = [];
}