<?php

namespace PhpBoot\Entity;

interface BuilderInterface
{
    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function build($data, $validate = true);
}