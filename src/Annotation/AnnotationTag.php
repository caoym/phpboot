<?php

namespace PhpBoot\Annotation;


class AnnotationTag implements \ArrayAccess
{
    /**
     * AnnotationTag constructor.
     * @param string $name
     * @param string $summary
     * @param string $description
     * @param array $children
     * @param AnnotationBlock|AnnotationTag|null $parent
     */
    public function __construct($name='',
                                $description='',
                                $children=[],
                                $parent=null)
    {
        $this->name = $name;
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
    public $description='';
    /**
     * @var AnnotationBlock[]
     */
    public $children=[];

    /**
     * @var AnnotationBlock|null
     */
    public $parent;

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}