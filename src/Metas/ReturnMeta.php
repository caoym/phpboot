<?php

namespace PhpBoot\Metas;

use PhpBoot\Entity\ContainerInterface;
use PhpBoot\Entity\TypeContainerInterface;

class ReturnMeta
{
    public function __construct($source, $type, $description, $container)
    {
        $this->source = $source;
        $this->type = $type;
        $this->description = $description;
        $this->container = $container;
    }

    /**
     * @var string
     * 返回值来源,语法 http://jmespath.org/tutorial.html
     * 目前支持的返回值来源包括: return的返回值, &引用变量的输出, 常量
     * 分别用return 和params, `常量`
     */
    public $source;

    /**
     * @var string 返回值类型
     */
    public $type;

    /**
     * @var string
     */
    public $description;

    /**
     * @var TypeContainerInterface|null
     */
    public $container;
}