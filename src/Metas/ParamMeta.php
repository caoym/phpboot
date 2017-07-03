<?php

namespace PhpBoot\Metas;
use PhpBoot\Entity\ArrayBuilder;
use PhpBoot\Entity\BuilderInterface;
use PhpBoot\Entity\EntityBuilder;
use PhpBoot\Entity\ScalarTypeBuilder;

/**
 * Class ParamMeta
 * @package Once\route
 * 函数参数元信息
 */
class ParamMeta{

    /**
     * ParamMeta constructor.
     * @param string $name
     * @param string $source
     * @param string $type
     * @param boolean $isOptional 是否可选, 如果可选, 则
     * @param mixed|null $default
     * @param boolean $isPassedByReference
     * @param string $validation
     * @param string $description
     * @param BuilderInterface|null $builder
     */
    public function __construct($name, $source, $type, $isOptional ,$default, $isPassedByReference,$validation, $description="", $builder=null){
        $this->name = $name;
        $this->source = $source;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->isPassedByReference = $isPassedByReference;
        $this->validation = $validation;
        $this->description = $description;
        $this->builder = $builder;
    }
    public $name;
    public $source;
    public $type;
    public $default;
    public $isOptional;
    public $doc;
    public $isPassedByReference;
    public $validation;
    public $description;
    /**
     * @var BuilderInterface|null
     */
    public $builder;
}