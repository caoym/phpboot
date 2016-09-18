<?php
/**
 * Created by PhpStorm.
 * User: caoyangmin
 * Date: 16/8/21
 * Time: 下午2:58
 */

namespace phprs\ezsql;

if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}
class Rows
{
    static function column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }
    static public function leftJoin(&$lh, $rh, $lKey,$rkey,$destKey){
        $map = array_combine(self::column($rh,$rkey),$rh);

        foreach ($lh as &$v){
            $v[$destKey]=$map[$v[$lKey]];
        }
    }
}