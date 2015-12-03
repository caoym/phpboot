<?php

namespace Peekmo\JsonPath;

/* JSONStore 0.5 - JSON structure as storage
*
* Copyright (c) 2007 Stefan Goessner (goessner.net)
* Licensed under the MIT (MIT-LICENSE.txt) licence.
*
* Modified by Axel Anceau
*/

class JsonStore
{
    private static $emptyArray = array();

    /**
     * @var array
     */
    private $data;

    /**
     * @var JsonPath
     */
    private $jsonPath;

    /**
     * @param string|array|\stdClass $data
     */
    public function __construct($data)
    {
        $this->jsonPath = new JsonPath();
        $this->setData($data);
    }

    /**
     * Sets JsonStore's manipulated data
     * @param string|array|\stdClass $data
     */
    public function setData($data)
    {
        $this->data = $data;

        if (is_string($this->data)) {
            $this->data = json_decode($this->data, true);
        } else if (is_object($data)) {
            $this->data = json_decode(json_encode($this->data), true);
        } else if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data type in JsonStore. Expected object, array or string, got %s', gettype($data)));
        }
    }

    /**
     * JsonEncoded version of the object
     * @return string
     */
    public function toString()
    {
        return json_encode($this->data);
    }

    /**
     * Returns the given json string to object
     * @return \stdClass
     */
    public function toObject()
    {
        return json_decode(json_encode($this->data));
    }

    /**
     * Returns the given json string to array
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Gets elements matching the given JsonPath expression
     * @param string $expr JsonPath expression
     * @param bool $unique Gets unique results or not
     * @param bool $create create if not found
     * @return array
     */
    public function get($expr, $unique = false, $create = false, $default = null)
    {
        if ((($exprs = $this->normalizedFirst($expr,$create,$default)) !== false) &&
            (is_array($exprs) || $exprs instanceof \Traversable)
        ) {
            $values = array();

            foreach ($exprs as $expr) {
                $o =& $this->data;
                $keys = preg_split(
                    "/([\"'])?\]\[([\"'])?/",
                    preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr)
                );

                for ($i = 0; $i < count($keys); $i++) {
                    $o =& $o[$keys[$i]];
                }

                $values[] = & $o;
            }

            if (true === $unique) {
                if (!empty($values) && is_array($values[0])) {
                    array_walk($values, function(&$value) {
                        $value = json_encode($value);
                    });

                    $values = array_unique($values);
                    array_walk($values, function(&$value) {
                        $value = json_decode($value, true);
                    });

                    return array_values($values);
                }

                return array_unique($values);
            }

            return $values;
        }

        return self::$emptyArray;
    }

    /**
     * Sets the value for all elements matching the given JsonPath expression
     * @param string $expr JsonPath expression
     * @param mixed $value Value to set
     * @return bool returns true if success
     */
    function set($expr, $value)
    {
        if ($res = $this->get($expr, false, true, null)) {
            foreach ($res as &$r) {
                $r = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Adds one or more elements matching the given json path expression
     * @param string $parentexpr JsonPath expression to the parent
     * @param mixed $value Value to add
     * @param string $name Key name
     * @return bool returns true if success
     */
    public function add($parentexpr, $value, $name = "")
    {
        if ($parents = $this->get($parentexpr)) {

            foreach ($parents as &$parent) {
                $parent = is_array($parent) ? $parent : array();

                if ($name != "") {
                    $parent[$name] = $value;
                } else {
                    $parent[] = $value;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Removes all elements matching the given jsonpath expression
     * @param string $expr JsonPath expression
     * @return bool returns true if success
     */
    public function remove($expr)
    {
        if ((($exprs = $this->normalizedFirst($expr)) !== false) &&
            (is_array($exprs) || $exprs instanceof \Traversable)
        ) {
            foreach ($exprs as &$expr) {
                $o =& $this->data;
                $keys = preg_split(
                    "/([\"'])?\]\[([\"'])?/",
                    preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr)
                );
                for ($i = 0; $i < count($keys) - 1; $i++) {
                    $o =& $o[$keys[$i]];
                }

                unset($o[$keys[$i]]);
            }

            return true;
        }

        return false;
    }

    private function normalizedFirst($expr, $create,$default)
    {
        if ($expr == "") {
            return false;
        } else {
            if (preg_match("/^\$(\[([0-9*]+|'[-a-zA-Z0-9_ ]+')\])*$/", $expr)) {
                print("normalized: " . $expr);

                return $expr;
            } else {
                $res = $this->jsonPath->jsonPath($this->data, $expr, array("resultType" => "PATH"), $create,$default);

                return $res;
            }
        }
    }
}

?>
