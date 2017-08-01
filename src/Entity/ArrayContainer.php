<?php

namespace PhpBoot\Entity;


use PhpBoot\Utils\TypeHint;

class ArrayContainer implements TypeContainerInterface
{
    /**
     * @param string $type
     * @param callable $getElementContainer
     * @return self
     */
    static public function create($type, callable $getElementContainer)
    {
        TypeHint::isArray($type) or \PhpBoot\abort(new \InvalidArgumentException("type $type is not array"));
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
        is_array($data) or \PhpBoot\abort(new \InvalidArgumentException('the first param is required to be array'));
        $res = [];
        foreach ($data as $k=>$v){
            $res[$k] = $this->container->make($v, $validate);
        }
        return $res;
    }
    public function makeExample()
    {
        return [$this->container->makeExample()];
    }
    /**
     * @return TypeContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @var TypeContainerInterface
     */
    private $container;
}