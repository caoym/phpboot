<?php

namespace PhpBoot\Metas;
use PhpBoot\Entity\ArrayContainer;
use PhpBoot\Entity\ContainerInterface;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Entity\ScalarTypeContainer;
use PhpBoot\Entity\TypeContainerInterface;

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
     * @param TypeContainerInterface|null $container
     */
    public function __construct($name, $source, $type, $isOptional ,$default, $isPassedByReference,$validation, $description="", $container=null){
        $this->name = $name;
        $this->source = $source;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->isPassedByReference = $isPassedByReference;
        $this->validation = $validation;
        $this->description = $description;
        $this->container = $container;
    }
    public $name;
    public $source;
    public $type;
    public $default;
    public $isOptional;
    public $isPassedByReference;
    public $validation;
    public $description;
    /**
     * @var TypeContainerInterface|null
     */
    public $container;
}