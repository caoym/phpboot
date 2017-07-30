<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Process\ProcessContext;

interface ProcessTaskInterface
{
    public function handle(ProcessContext $context);

    /**
     * 判断是否需要对此节点进行持久化
     *
     * 不进行持久化, 如果过程异常结束, 则无法记录此节点是否已经执行, 过程重启后, 将从最后一个持久化的节点开始执行;但开启持久化会降低系
     * 统吞吐量。
     *
     * 建议幂等的(允许重试的)接口不开启, 而只对不幂等的接口开启。
     * @return bool
     */
    public function needPersistence();
}