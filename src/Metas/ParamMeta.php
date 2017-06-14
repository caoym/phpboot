<?php

namespace PhpBoot\Metas;

/**
 * Class ParamMeta
 * @package Once\route
 * 函数参数元信息
 */
class ParamMeta{

    /**
     * ParamMeta constructor.
     * @param string $name
     * @param string $source 来源, 使用jsonpath描述 @see peekmo/jsonpath
     * @param string $type
     * @param boolean $isOptional 是否可选, 如果可选, 则
     * @param mixed|null $default
     * @param boolean $isPassedByReference
     * @param string $validation
     * @param string $doc
     */
    public function __construct($name, $source, $type, $isOptional ,$default, $isPassedByReference,$validation, $doc=""){
        $this->name = $name;
        $this->source = $source;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->isPassedByReference = $isPassedByReference;
        $this->validation = $validation;
        $this->doc = $doc;
    }
    public $name;
    public $source;
    public $type;
    public $default;
    public $isOptional;
    public $doc;
    public $isPassedByReference;
    public $validation;
}