<?php

namespace PhpBoot\Entity;

class MixedTypeContainer implements ContainerInterface
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
}