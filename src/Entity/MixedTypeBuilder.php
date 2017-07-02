<?php

namespace PhpBoot\Entity;

class MixedTypeBuilder implements BuilderInterface
{

    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function build($data, $validate = true)
    {
        return $data;
    }
}