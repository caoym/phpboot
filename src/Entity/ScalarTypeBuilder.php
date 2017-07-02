<?php

namespace PhpBoot\Entity;

use PhpBoot\Utils\TypeCast;
use PhpBoot\Utils\TypeHint;

class ScalarTypeBuilder implements BuilderInterface
{
    public function __construct($type)
    {
        $this->type = $type;
        !$type || TypeHint::isScalarType($type)  or fail(new \InvalidArgumentException("$type is not scalar type"));
    }

    public function build($data, $validate = true){
        return TypeCast::cast($data, $this->type, $validate);
    }
    private $type;
}