<?php

namespace PhpBoot\Metas;

use PhpBoot\Entity\EntityBuilder;

class PropertyMeta
{
    /**
     * PropertyMeta constructor.
     * @param string $name
     * @param string $type
     * @param boolean $isOptional
     * @param mixed|null $default
     * @param string $validation
     * @param string $summary
     * @param string $description
     * @param EntityBuilder $builder
     */
    public function __construct($name, $type=null, $isOptional=false,$default=null, $validation=null, $summary='', $description='', $builder = null){
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->validation = $validation;
        $this->summary = $summary;
        $this->description = $description;
        $this->builder = $builder;
    }

    /**
     * @var EntityBuilder
     */
    public $builder;
    public $name;
    public $type;
    public $default;
    public $isOptional;
    public $validation;
    /**
     * @var string
     */
    public $summary = '';
    /**
     * @var string
     */
    public $description='';
}