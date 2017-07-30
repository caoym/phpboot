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
trait SingleInput{
    public function preConnect(ConnectedAble $from)
    {
        count($this->getInputs()) == 0
            or \PhpBoot\abort(new ProcessDefineException("connect {$from->getName()} to {$this->getName()} failed, an ".get_class($this)." can only have ONE input"));
        parent::preConnect($from);
    }
}