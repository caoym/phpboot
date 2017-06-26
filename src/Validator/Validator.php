<?php
namespace PhpBoot\Validator;
use PhpBoot\Annotation\EntityMetaLoader;
use PhpBoot\Utils\TypeHint;

/**
 * Class Validator
 */
class Validator extends \Valitron\Validator
{
    /**
     * @param callable|string $rule
     * @param array|string $fields
     * @return $this
     */
    public function rule($rule, $fields)
    {
        if(is_string($rule)){
            $rules = explode('|', $rule);
            foreach ($rules as $r){
                $params = explode(':', trim($r));
                $params = array_merge([$params[0],$fields], array_slice($params,1));
                call_user_func_array([$this, 'parent::rule'], $params);
            }
            return $this;
        }
        parent::rule($rule, $fields);
        return $this;
    }

    /**
     * Validate that a field matches a specified type
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @internal param array $fields
     * @return bool
     */
    protected function validateType($field, $value, $params)
    {
        $type = $params[0];
        if(TypeHint::isArray($type)){
            $type = TypeHint::getArrayType($type);
            if(!$this->validateArray($field, $value)){
                return false;
            }
            foreach ($value as $k=>$v){
                if(!$this->validateType($field.'.'.$k, $v, [$type])){
                    return false;
                }
            }
            return true;
        }
        if(TypeHint::isScalarType($type)){
            if($type == 'mixed'){
                return true;
            }else{
                return call_user_func("is_$type", $value);
            }
        }else{
            //TODO class validate
            $metas = new EntityMetaLoader($type);
            $metas = $metas->getPropertyMetas();

        }
    }

}