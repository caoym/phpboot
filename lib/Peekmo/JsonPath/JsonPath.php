<?php

namespace Peekmo\JsonPath;


/* JSONPath 0.8.3 - XPath for JSON
*
* Copyright (c) 2007 Stefan Goessner (goessner.net)
* Licensed under the MIT (MIT-LICENSE.txt) licence.
*
* Modified by Axel Anceau
*/


class JsonPath
{
    private $obj = null;
    private $resultType = "Value";
    private $result = array();
    private $subx = array();
    private $keywords = array('=', ')', '!', '<', '>');

    public function jsonPath(&$obj, $expr, $args = null, $create=false, $default=null)
    {
        if (is_object($obj)) {
            throw new \Exception('You sent an object, not an array.');
        }

        $this->resultType = ($args ? $args['resultType'] : "VALUE");
        $x = $this->normalize($expr);

        $this->obj = $obj;
        if ($expr && $obj!==null && ($this->resultType == "VALUE" || $this->resultType == "PATH")) {
            $this->trace(preg_replace("/^\\$;/", "", $x), $obj, "$", $create, $default);
            if (count($this->result)) {
                return $this->result;
            }

            return false;
        }
    }

    // normalize path expression
    private function normalize($expression)
    {
        // Replaces filters by #0 #1...
        $expression = preg_replace_callback(
            array("/[\['](\??\(.*?\))[\]']/", "/\['(.*?)'\]/"),
            array(&$this, "tempFilters"),
            $expression
        );

        // ; separator between each elements
        $expression = preg_replace(
            array("/'?\.'?|\['?/", "/;;;|;;/", "/;$|'?\]|'$/"),
            array(";", ";..;", ""),
            $expression
        );

        // Restore filters
        $expression = preg_replace_callback("/#([0-9]+)/", array(&$this, "restoreFilters"), $expression);
        $this->result = array(); // result array was temporarily used as a buffer ..
        return $expression;
    }

    /**
     * Pushs the filter into the list
     * @param string $filter
     * @return string
     */
    private function tempFilters($filter)
    {
        $f = $filter[1];
        $elements = explode('\'', $f);

        // Hack to make "dot" works on filters
        for ($i=0, $m=0; $i<count($elements); $i++) {
            if ($m%2 == 0) {
                if ($i > 0 && substr($elements[$i-1], 0, 1) == '\\') {
                    continue;
                }

                $e = explode('.', $elements[$i]);
                $str = ''; $first = true;
                foreach ($e as $substr) {
                    if ($first) {
                        $str = $substr;
                        $first = false;
                        continue;
                    }

                    $end = null;
                    if (false !== $pos = $this->strpos_array($substr, $this->keywords)) {
                        list($substr, $end) = array(substr($substr, 0, $pos), substr($substr, $pos, strlen($substr)));
                    }

                    $str .= '[' . $substr . ']';
                    if (null !== $end) {
                        $str .= $end;
                    }
                }
                $elements[$i] = $str;
            }

            $m++;
        }

        return "[#" . (array_push($this->result, implode('\'', $elements)) - 1) . "]";
    }

    /**
     * Get a filter back
     * @param string $filter
     * @return mixed
     */
    private function restoreFilters($filter)
    {
        return $this->result[$filter[1]];
    }

    /**
     * Builds json path expression
     * @param string $path
     * @return string
     */
    private function asPath($path)
    {
        $expr = explode(";", $path);
        $fullPath = "$";
        for ($i = 1, $n = count($expr); $i < $n; $i++) {
            $fullPath .= preg_match("/^[0-9*]+$/", $expr[$i]) ? ("[" . $expr[$i] . "]") : ("['" . $expr[$i] . "']");
        }

        return $fullPath;
    }

    private function store($p, $v)
    {
        if ($p) {
            array_push($this->result, ($this->resultType == "PATH" ? $this->asPath($p) : $v));
        }

        return !!$p;
    }

    private function trace($expr, &$val, $path, $create=false, $default=null)
    {
        if ($expr !== "") {
            $x = explode(";", $expr);
            $loc = array_shift($x);
            $x = implode(";", $x);
            
            if (is_array($val) && array_key_exists($loc, $val)) {
                $this->trace($x, $val[$loc], $path . ";" . $loc, $create, $default);
            }
            else if ($loc == "*") {
                $this->walk($loc, $x, $val, $path, array(&$this, "_callback_03"));
            }
            else if ($loc === "..") {
                $this->trace($x, $val, $path,$create, $default);
                $this->walk($loc, $x, $val, $path, array(&$this, "_callback_04"));
            }
            else if (preg_match("/^\(.*?\)$/", $loc)) { // [(expr)]
                $this->trace($this->evalx($loc, $val, substr($path, strrpos($path, ";") + 1)) . ";" . $x, $val, $path,$create, $default);
            }
            else if (preg_match("/^\?\(.*?\)$/", $loc)) { // [?(expr)]
                $this->walk($loc, $x, $val, $path, array(&$this, "_callback_05"));
            }
            else if (preg_match("/^(-?[0-9]*):(-?[0-9]*):?(-?[0-9]*)$/", $loc)) { // [start:end:step]  phyton slice syntax
                $this->slice($loc, $x, $val, $path,$create, $default);
            }
            else if (preg_match("/,/", $loc)) { // [name1,name2,...]
                for ($s = preg_split("/'?,'?/", $loc), $i = 0, $n = count($s); $i < $n; $i++)
                    $this->trace($s[$i] . ";" . $x, $val, $path, $create, $default);
            }
            else if (is_array($val) && $create && ! array_key_exists($loc, $val)) {
                if ($x !== "") {
                    $val[$loc] = array();
                } else {
                    $val[$loc] = $default;
                }
                $this->trace($x, $val[$loc], $path . ";" . $loc, $create, $default);
            }
        } else {
            $this->store($path, $val);
        }
    }

    private function _callback_03($m, $l, $x, $v, $p)
    {
        $this->trace($m . ";" . $x, $v, $p);
    }


    private function _callback_04($m, $l, $x, $v, $p)
    {
        if (is_array($v[$m])) {
            $this->trace("..;" . $x, $v[$m], $p . ";" . $m);
        }
    }

    private function _callback_05($m, $l, $x, $v, $p)
    {
        if ($this->evalx(preg_replace("/^\?\((.*?)\)$/", "$1", $l), $v[$m])) {
            $this->trace($m . ";" . $x, $v, $p);
        }
    }

    private function walk($loc, $expr, $val, $path, $f)
    {
        foreach ($val as $m => $v) {
            call_user_func($f, $m, $loc, $expr, $val, $path);
        }
    }

    private function slice($loc, $expr, &$v, $path,$create=false, $default=null)
    {
        $s = explode(":", preg_replace("/^(-?[0-9]*):(-?[0-9]*):?(-?[0-9]*)$/", "$1:$2:$3", $loc));
        $len = count($v);
        $start = (int)$s[0] ? $s[0] : 0;
        $end = (int)$s[1] ? $s[1] : $len;
        if($create){
            if($start>=$end){
                $end = $start+1;
            }
        }
        $step = (int)$s[2] ? $s[2] : 1;
        if($create){
            $start = ($start < 0) ? max(0, $start + $len) : $start;
            $end = ($end < 0) ? max(0, $end + $len) : $end;
        }else{
            $start = ($start < 0) ? max(0, $start + $len) : min($len, $start);
            $end = ($end < 0) ? max(0, $end + $len) : min($len, $end);
        }
        
        for ($i = $start; $i < $end; $i += $step) {
            $this->trace($i . ";" . $expr, $v, $path, $create, $default);
        }
    }

    /**
     * @param string $x filter
     * @param array $v node
     *
     * @param string $vname
     * @return string
     */
    private function evalx($x, $v, $vname = null)
    {
        $name = "";
        $expr = preg_replace(array("/\\$/", "/@/"), array("\$this->obj", "\$v"), $x);
        $res = eval("\$name = $expr;");

        if ($res === false) {
            print("(jsonPath) SyntaxError: " . $expr);
        } else {
            return $name;
        }
    }

    private function toObject($array)
    {
        //$o = (object)'';
        $o = new \stdClass();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->toObject($value);
            }

            $o->$key = $value;
        }

        return $o;
    }

    /**
     * Search one of the given needs in the array
     * @param string $haystack
     * @param array $needles
     * @return bool|string
     */
    private function strpos_array($haystack, array $needles)
    {
        $closer = 10000;
        foreach($needles as $needle) {
            if (false !== $pos = strpos($haystack, $needle)) {
                if ($pos < $closer) {
                    $closer = $pos;
                }
            }
        }

        return 10000 === $closer ? false : $closer;
    }
}

?>