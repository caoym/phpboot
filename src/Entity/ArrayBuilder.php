<?php

namespace PhpBoot\Entity;


use PhpBoot\Utils\TypeHint;

class ArrayBuilder implements BuilderInterface
{
    /**
     * @param string $type
     * @param callable $getElementBuilder
     * @return ArrayBuilder
     */
    static public function create($type, callable $getElementBuilder)
    {
        TypeHint::isArray($type) or fail(new \InvalidArgumentException("type $type is not array"));
        $elementType = $type;
        $loops = 0;
        while(TypeHint::isArray($elementType)){
            $elementType = TypeHint::getArrayType($elementType);
            $loops++;
        }
        $builder = $getElementBuilder($elementType);

        while($loops--){
            $builder = new ArrayBuilder($builder);
        }
        return $builder;
    }
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