<?php

namespace PhpBoot\Entity;

interface TypeContainerInterface
{
    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function make($data, $validate = true);

    public function makeExample();
}