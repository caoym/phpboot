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
        TypeHint::isScalarType($type) or \PhpBoot\abort(new \InvalidArgumentException("$type is not scalar type"));

        if(is_bool($val)){
            $val = intval($val);
        }else if($val === null){
            $map = [
                'string'=>'',
                'bool'=>false,
                'int'=>0,
                'float'=>0,
            ];
            if(isset($map[$type])){
                $val = $map[$type];
            }
        }
        if(is_object($val)){
            try{
                $val = (string)$val;
            }catch (\Exception $e){
                $className = get_class($val);
                \PhpBoot\abort(new \InvalidArgumentException("could not cast value from class $className to {$type}"));
            }

        }
        if(is_array($val)){
            $type == 'array' ||  $type =='mixed' || !$type or \PhpBoot\abort(new \InvalidArgumentException("could not cast value from resource to {$type}"));
        }
        if(is_resource($val)) {
            \PhpBoot\abort(new \InvalidArgumentException("could not cast value from resource to {$type}"));
        }
        if(!$validate){
            settype($val, $type) or \PhpBoot\abort(new \InvalidArgumentException("cast value($val) to {$type} failed"));
        }else{
            $ori = $val;
            $oriType = gettype($val);
            settype($val, $type) or \PhpBoot\abort(new \InvalidArgumentException("cast value($ori) to type {$type} failed"));
            $newData = $val;
            if(is_bool($newData)){
                $newData = intval($newData);
            }
            settype($newData, $oriType) or \PhpBoot\abort(new \InvalidArgumentException("cast value($ori) to type {$type} failed"));
            if($ori != $newData){
                \PhpBoot\abort(new \InvalidArgumentException("could not cast value($ori) to type {$type}"));
            }
        }
        return $val;
    }
}