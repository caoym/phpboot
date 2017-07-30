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
trait NoOutput{

    public function connectTo(ConnectedAble $next)
    {
        \PhpBoot\abort(new ProcessDefineException("can not connect from a ".get_class($this)));
    }
}