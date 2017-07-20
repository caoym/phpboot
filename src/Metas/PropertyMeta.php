<?php

namespace PhpBoot\Metas;

use PhpBoot\Entity\ArrayContainer;
use PhpBoot\Entity\EntityContainer;
use PhpBoot\Entity\ScalarTypeContainer;
use PhpBoot\Entity\TypeContainerInterface;

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
     * @param TypeContainerInterface|null $container
     */
    public function __construct($name, $type=null, $isOptional=false,$default=null, $validation=null, $summary='', $description='', $container = null){
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->isOptional = $isOptional;
        $this->validation = $validation;
        $this->summary = $summary;
        $this->description = $description;
        $this->container = $container;
    }

    /**
     * @var TypeContainerInterface|null
     */
    public $container;
    public $name;
    public $type;
    public $default;
    public $isOptional;
    /**
     * 如
     * "in:0,1,2"
     * [*.num, "in:0,1,2"]
     *
     * @var array|string
     */
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