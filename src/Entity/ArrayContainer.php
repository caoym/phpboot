<?php

namespace PhpBoot\Entity;


use PhpBoot\Utils\TypeHint;

class ArrayContainer implements ContainerInterface
{
    /**
     * @param string $type
     * @param callable $getElementContainer
     * @return self
     */
    static public function create($type, callable $getElementContainer)
    {
        TypeHint::isArray($type) or fail(new \InvalidArgumentException("type $type is not array"));
        $elementType = $type;
        $loops = 0;
        while(TypeHint::isArray($elementType)){
            $elementType = TypeHint::getArrayType($elementType);
            $loops++;
        }
        $container = $getElementContainer($elementType);

        while($loops--){
            $container = new self($container);
        }
        return $container;
    }
    public function __construct($elementContainer)
    {
        $this->container = $elementContainer;
    }

    /**
     * @param mixed $data
     * @param bool $validate
     * @return mixed
     */
    public function make($data, $validate = true)
    {
        is_array($data) or fail(new \InvalidArgumentException('the first param is required to be array'));
        $res = [];
        foreach ($data as $k=>$v){
            $res[$k] = $this->container->make($v, $validate);
        }
        return $res;
    }

    /**
     * @var ContainerInterface
     */
    private $container;
}