<?php

namespace PhpBoot\Entity;

class MixedTypeContainer implements TypeContainerInterface
{
    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function make($data, $validate = true)
    {
        return $data;
    }
    public function makeExample()
    {
        return 'Any';
    }
}