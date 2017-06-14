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
    public function __construct($name, $type, $isOptional ,$default, $validation, $doc=""){
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->validation = $validation;
        $this->doc = $doc;
    }
    public $name;
    public $type;
    public $default;
    public $isOptional;
    public $validation;
    public $doc;
}