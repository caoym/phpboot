<?php

namespace PhpBoot\Workflow\Process\Nodes;

use PhpBoot\Workflow\Process\Traits\NoInput;
use PhpBoot\Workflow\Process\Traits\SingleOutput;


class BeginEvent extends ProcessNode
{
    use NoInput;
    use SingleOutput;
}