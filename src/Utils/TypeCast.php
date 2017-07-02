<?php

namespace PhpBoot\Utils;

class TypeCast
{
    /**
     * @param mixed $val
     * @param string $type
     * @param bool $validate
     * @return mixed
     */
    static public function cast($val, $type, $validate = true)
    {
        TypeHint::isScalarType($type) or fail(new \InvalidArgumentException("$type is not scalar type"));

        if(is_bool($val)){
            $val = intval($val);
        }
        if(is_object($val)){
            try{
                $val = (string)$val;
            }catch (\Exception $e){
                $className = get_class($val);
                fail(new \InvalidArgumentException("could not cast value from class $className to {$type}"));
            }

        }
        if(is_array($val)){
            $type == 'array' ||  $type =='mixed' || !$type or fail(new \InvalidArgumentException("could not cast value from resource to {$type}"));
        }
        if(is_resource($val)) {
            fail(new \InvalidArgumentException("could not cast value from resource to {$type}"));
        }
        if(!$validate){
            settype($val, $type) or fail(new \InvalidArgumentException("cast value($val) to {$type} failed"));
        }else{
            $ori = $val;
            $oriType = gettype($val);
            settype($val, $type) or fail(new \InvalidArgumentException("cast value($ori) to type {$type} failed"));
            $newData = $val;
            if(is_bool($newData)){
                $newData = intval($newData);
            }
            settype($newData, $oriType) or fail(new \InvalidArgumentException("cast value($ori) to type {$type} failed"));
            if($ori != $newData){
                fail(new \InvalidArgumentException("could not cast value($ori) to type {$type}"));
            }
        }
        return $val;
    }
}