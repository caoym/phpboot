<?php
namespace PhpBoot\Utils;

class ArrayHelper
{
    /**
     * TODO 支持""转意
     * @param array|\ArrayAccess $arr
     * @param string $key like key1.key2.key3
     * @param mixed $val
     */
    static public function set(&$arr, $key, $val)
    {
        $arr instanceof \ArrayAccess || is_array($arr) or \PhpBoot\abort(new \InvalidArgumentException('the first param require array or object of ArrayAccess'));
        $keys = explode('.', $key);
        $keys = array_reverse($keys);
        $cur = &$arr;
        while($p = array_pop($keys)){
            $cur instanceof \ArrayAccess || is_array($cur) or \PhpBoot\abort(new \InvalidArgumentException('array or object of ArrayAccess required'));
            if(!isset($cur[$p])){
                if(count($keys) == 0){
                    $cur[$p] = $val;
                }else{
                    $cur[$p] = [];
                }
            }else{
                if(count($keys) == 0){
                    $cur[$p] = $val;
                }
            }
            $cur = &$cur[$p];
        }
    }

}