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
trait NoInput{
    public function preConnect(ConnectedAble $from)
    {
        \PhpBoot\abort(new ProcessDefineException("can not connect a ".get_class($this)));
    }

    public function postConnect(ConnectedAble $from)
    {
        \PhpBoot\abort(new ProcessDefineException("can not connect a ".get_class($this)));
    }
}