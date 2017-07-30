<?php

namespace PhpBoot\Workflow\Process\Traits;

use PhpBoot\Workflow\Process\Nodes\ConnectedAble;
use PhpBoot\Workflow\Exceptions\ProcessDefineException;

/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 2017/3/3
 * Time: 下午7:11
 */
trait SingleOutput{

    public function connectTo(ConnectedAble $next)
    {
        count($this->getOutputs()) == 0 or \PhpBoot\abort(new ProcessDefineException("connect {$this->getName()} to {$next->getName()} failed, an ".get_class($this)." can only have ONE output"));
        parent::connectTo($next);
    }
}