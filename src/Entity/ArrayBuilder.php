<?php

namespace PhpBoot\Entity;


class ArrayBuilder implements BuilderInterface
{
    public function __construct($elementBuilder)
    {
        $this->builder = $elementBuilder;
    }

    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function build($data, $validate = true)
    {
        is_array($data) or fail(new \InvalidArgumentException('the first param is required to be array'));
        $res = [];
        foreach ($data as $k=>$v){
            $res[$k] = $this->builder->build($v, $validate);
        }
        return $res;
    }

    /**
     * @var BuilderInterface
     */
    private $builder;
}