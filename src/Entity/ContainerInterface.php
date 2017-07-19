<?php

namespace PhpBoot\Entity;

interface ContainerInterface
{
    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function make($data, $validate = true);
}