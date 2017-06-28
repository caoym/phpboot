<?php

namespace PhpBoot\Metas;

class PropertyMeta
{
    /**
     * PropertyMeta constructor.
     * @param string $name
     * @param string $type
     * @param boolean $isOptional
     * @param mixed|null $default
     * @param string $validation
     * @param string $doc
     */
    public function __construct($name, $type=null, $isOptional=false,$default=null, $validation=null, $summary='', $description=''){
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->validation = $validation;
        $this->summary = $summary;
        $this->description = $description;
    }
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