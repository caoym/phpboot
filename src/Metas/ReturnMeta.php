<?php

namespace PhhBoot\Metas;

class ReturnMeta
{
    public function __construct($source, $type, $doc)
    {
        $this->source = $source;
        $this->type = $type;
        $this->doc = $doc;
    }

    /**
     * @var string
     * 返回值来源, 使用jsonpath描述 @see peekmo/jsonpath
     * 目前支持的返回值来源包括: return的返回值, &引用变量的输出, 常量
     * 分别用$.return 和$.params, 和不带$前缀的文本
     */
    public $source;

    /**
     * @var string 返回值类型
     */
    public $type;

    /**
     * @var string
     */
    public $doc;
}