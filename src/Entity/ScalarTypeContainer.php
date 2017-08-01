<?php

namespace PhpBoot\Entity;

use PhpBoot\Utils\TypeCast;
use PhpBoot\Utils\TypeHint;

class ScalarTypeContainer implements TypeContainerInterface
{
    public function __construct($type)
    {
        $this->type = $type;
        !$type || TypeHint::isScalarType($type)  or \PhpBoot\abort(new \InvalidArgumentException("$type is not scalar type"));
    }

    public function make($data, $validate = true){
        return TypeCast::cast($data, $this->type, $validate);
    }

    public function makeExample()
    {
        return TypeCast::cast(null, $this->type, false);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    private $type;
}