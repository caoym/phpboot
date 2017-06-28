<?php

namespace PhpBoot\Annotation;


class AnnotationBlock implements \ArrayAccess
{
    /**
     * AnnotationBlock constructor.
     * @param string $name
     * @param string $summary
     * @param string $description
     * @param AnnotationTag[] $children
     * @param AnnotationBlock|null $parent
     */
    public function __construct($name='',
                                $summary='',
                                $description='',
                                $children=[],
                                $parent=null)
    {
        $this->name = $name;
        $this->summary = $summary;
        $this->description = $description;
        $this->children = $children;
        $this->parent = $parent;
    }

    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $summary = '';
    /**
     * @var string
     */
    public $description='';
    /**
     * @var AnnotationTag[]
     */
    public $children=[];

    /**
     * @var AnnotationTag|null
     */
    public $parent;


    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }


    public function offsetGet($offset)
    {
        return $this->$offset;
    }


    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }


    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}