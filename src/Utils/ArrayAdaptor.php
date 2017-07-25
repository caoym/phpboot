<?php
namespace PhpBoot\Utils;

class ArrayAdaptor implements \ArrayAccess
{
    /**
     * ArrayAdaptor constructor.
     * @param object|array $obj
     */
    public function __construct(&$obj)
    {
        $this->obj = &$obj;
    }

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
        if(is_array($this->obj)){
            return array_key_exists($offset, $this->obj);
        }elseif(self::hasProperty($this->obj, $offset)){
            return true;
        }elseif(method_exists($this->obj, 'has')){
            return $this->obj->has($offset);
        }elseif(method_exists($this->obj, $method = 'has'.ucfirst($offset))){
            return $this->obj->{$method}($offset);
        }elseif(method_exists($this->obj, $method = 'get'.ucfirst($offset))){
            return $this->obj->{$method}() !== null;
        }elseif(method_exists($this->obj, 'get')){
            return $this->obj->get($offset) !== null;
        }else{
            return false;
        }
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
        $res = null;
        if(is_array($this->obj)){
            $res = &$this->obj[$offset];
        }elseif(self::hasProperty($this->obj, $offset)){
            $res = &$this->obj->{$offset};
        }elseif(method_exists($this->obj, 'get')){
            $res = $this->obj->get($offset);
        }elseif(method_exists($this->obj, $method = 'get'.ucfirst($offset))){
            $res = $this->obj->{$method}();
        }else{
            throw new \InvalidArgumentException("offsetGet($offset) failed");
        }
        if(is_array($res) || is_object($res)){
            return new self($res);
        }
        return $res;
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
        if(is_array($this->obj)){
            $this->obj[$offset] = $value;
        }elseif(self::hasProperty($this->obj, $offset)){
            $this->obj->{$offset} = $value;
        }elseif(method_exists($this->obj, 'set')){
            $this->obj->set($offset, $value);
        }elseif(method_exists($this->obj, $method = 'set'.ucfirst($offset))){
            $this->obj->{$method}($value);
        }else{
            throw new \BadMethodCallException("can not set $offset");
        }
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
        if(is_array($this->obj)){
            unset($this->obj[$offset]);
        }elseif(self::hasProperty($this->obj, $offset)){
            unset($this->obj->{$offset});
        }elseif(method_exists($this->obj, 'remove')){
            $this->obj->remove($offset);
        }elseif(method_exists($this->obj, $method = 'remove'.ucfirst($offset))){
            $this->obj->$method();
        }else{
            throw new \InvalidArgumentException("offsetUnset($offset) failed");
        }
    }
    static public function strip($obj){
        if($obj instanceof self){
            return $obj->obj;
        }
        return $obj;
    }
    static function hasProperty($object, $name)
    {
        if(!is_object($object)){
            return false;
        }
        $class = new \ReflectionClass($object);
        if(!$class->hasProperty($name)){
            return false;
        }
        $property = $class->getProperty($name);
        if(!$property){
            return false;
        }
        return $property->isPublic();
    }
    private $obj;
}