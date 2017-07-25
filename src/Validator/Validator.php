<?php
namespace PhpBoot\Validator;
use PhpBoot\Annotation\EntityContainerBuilder;
use PhpBoot\Utils\TypeHint;


/**
 * Validator
 *
 * ** usage: **
 *  $v = new Validator();
 *  $v->rule('required|integer|in:1,2,3', 'fieldName');
 *
 * ** rules: **
 *
 * required - Required field
 * equals - Field must match another field (email/password confirmation)
 * different - Field must be different than another field
 * accepted - Checkbox or Radio must be accepted (yes, on, 1, true)
 * numeric - Must be numeric
 * integer - Must be integer number
 * boolean - Must be boolean
 * array - Must be array
 * length - String must be certain length
 * lengthBetween - String must be between given lengths
 * lengthMin - String must be greater than given length
 * lengthMax - String must be less than given length
 * min - Minimum
 * max - Maximum
 * in - Performs in_array check on given array values
 * notIn - Negation of in rule (not in array of values)
 * ip - Valid IP address
 * email - Valid email address
 * url - Valid URL
 * urlActive - Valid URL with active DNS record
 * alpha - Alphabetic characters only
 * alphaNum - Alphabetic and numeric characters only
 * slug - URL slug characters (a-z, 0-9, -, _)
 * regex - Field matches given regex pattern
 * date - Field is a valid date
 * dateFormat - Field is a valid date in the given format
 * dateBefore - Field is a valid date and is before the given date
 * dateAfter - Field is a valid date and is after the given date
 * contains - Field is a string and contains the given string
 * creditCard - Field is a valid credit card number
 * instanceOf - Field contains an instance of the given class
 * optional - Value does not need to be included in data array. If it is however, it must pass validation.
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
                $rule = $params[0];
                $params = isset($params[1])?explode(',', $params[1]):[];

                call_user_func_array([$this, 'parent::rule'], array_merge([$rule, $fields], $params));

            }
            return $this;
        }
        parent::rule($rule, $fields);
        return $this;
    }
    public function hasRule($name, $field)
    {
        return parent::hasRule($name, $field);
    }

//    /**
//     * Validate that a field matches a specified type
//     *
//     * @param  string $field
//     * @param  mixed  $value
//     * @param  array  $params
//     * @internal param array $fields
//     * @return bool
//     */
//    protected function validateType($field, $value, $params)
//    {
//        $type = $params[0];
//        if(TypeHint::isArray($type)){
//            $type = TypeHint::getArrayType($type);
//            if(!$this->validateArray($field, $value)){
//                return false;
//            }
//            foreach ($value as $k=>$v){
//                if(!$this->validateType($field.'.'.$k, $v, [$type])){
//                    return false;
//                }
//            }
//            return true;
//        }
//        if(TypeHint::isScalarType($type)){
//            if($type == 'mixed'){
//                return true;
//            }else{
//                return call_user_func("is_$type", $value);
//            }
//        }else{
//            //TODO class validate
//            $metas = new EntityContainerBuilder($type);
//            $metas = $metas->getPropertyMetas();
//
//        }
//    }

}