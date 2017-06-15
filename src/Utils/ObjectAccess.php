<?php
namespace PhpBoot\Utils;

/**
 * Class ObjectAccess
 * @package Once\Utils
 * 使用jsonpath语法访问对象属性, 支持基本的层次访问语法$.node1.node2
 * 支持访问对象的属性, get方法, 数组操作符[]
 *
 */
class ObjectAccess
{

    /**
     * ObjectAccess constructor.
     * @param object|array $obj
     * @param array $hookGet
     */
    public function __construct(&$obj, $hookGet=[]){
        $this->hookGet = $hookGet;
        $this->obj = &$obj;
    }

    public function has($path){
        if($this->obj==null || $path==null){
            return false;
        }
        $nodes = explode('.', $path);
        if(count($nodes)==0 || $nodes[0] != '$'){
            return false;
        }
        $nodes = array_slice($nodes, 1);
        if (!empty($this->hookGet)){ //处理取值hook
            $acs = new self($this->hookGet);
            $count = count($nodes);
            for ($i=0; $i<$count; $i++){
                $cur = array_slice($nodes, 0, $i+1);
                if($acs->hasByArray($this->hookGet, $cur)){
                    $value = $acs->getByArray($this->hookGet, $cur, null);
                    if($value instanceof \Closure){
                        $value = $value();
                        return $this->hasByArray($value, array_slice($nodes, $i+1));
                    }
                }
            }
        }

        return $this->hasByArray($this->obj, $nodes);

    }

    public function get($path, $default=null){
        if($this->obj==null || $path==null){
            return $default;
        }
        $nodes = explode('.', $path);
        if(count($nodes)==0 || $nodes[0] != '$'){
            return $default;
        }
        $nodes = array_slice($nodes, 1);
        if (!empty($this->hookGet)){ //处理取值hook
            $acs = new self($this->hookGet);
            $count = count($nodes);
            for ($i=0; $i<$count; $i++){
                $cur = array_slice($nodes, 0, $i+1);
                if($acs->hasByArray($this->hookGet, $cur)){
                    $value = $acs->getByArray($this->hookGet, $cur, null);
                    if($value instanceof \Closure){
                        $value = $value();
                        return $this->getByArray($value, array_slice($nodes, $i+1), $default);
                    }

                }
            }
        }
        return $this->getByArray($this->obj, $nodes,$default);

    }
    public function set($path, $val){
        $nodes = explode('.', $path);
        if(count($nodes)==0 || $nodes[0] != '$'){
            fail(new \InvalidArgumentException('invalid param 1'));
        }
        return $this->setByArray($this->obj, array_slice($nodes, 1), $val);

    }

    private function hasByArray($obj, $nodes){
        if(count($nodes)==0){
            return true;
        }
        if (is_array($obj) || $obj instanceof \ArrayAccess){
            if(!array_key_exists($nodes[0], $obj)){
                return false;
            }
            return $this->hasByArray($obj[$nodes[0]], array_slice($nodes,1));
        }elseif (is_object($obj)){
            if(method_exists($obj, $method = 'get'.ucwords($nodes[0]))){
                $val = $obj->{$method}();
                return $this->hasByArray($val, array_slice($nodes, 1));
            }elseif(property_exists($obj, $nodes[0])){
                return $this->hasByArray($obj->{$nodes[0]}, array_slice($nodes, 1));
            }else{
                return false;
            }

        }
        return false;
    }

    private function getByArray(&$obj, $nodes, $default){
        if(count($nodes)==0 ){
            return $obj;
        }

        if (is_array($obj) || $obj instanceof \ArrayAccess){
            if(!array_key_exists($nodes[0], $obj)){
                return $default;
            }
            return $this->getByArray($obj[$nodes[0]], array_slice($nodes,1), $default);
        }
        elseif (is_object($obj)){
            if(method_exists($obj, $method = 'get'.ucwords($nodes[0]))){
                $val = $obj->{$method}();
                return $this->getByArray($val, array_slice($nodes, 1), $default);
            }elseif(property_exists($obj, $nodes[0])){
                return $this->getByArray($obj->{$nodes[0]}, array_slice($nodes, 1), $default);
            }else{
                return $default;
            }
        }
        return $default;
    }

    //TODO 支持设置对象
    private function setByArray(&$obj, $nodes, $val){
        if(count($nodes)==0 ){
            $obj = $val;
            return true;
        }

        if (is_array($obj) || $obj instanceof \ArrayAccess){
            if(!array_key_exists($nodes[0], $obj)){
                $obj[$nodes[0]]=null;
            }
            return $this->setByArray($obj[$nodes[0]], array_slice($nodes,1), $val);
        }
        elseif (is_object($obj)){
            if(method_exists($obj, $method = 'get'.ucwords($nodes[0]))){
                $res = $obj->{$method}();
                return $this->setByArray($res, array_slice($nodes, 1), $val);
            }elseif(property_exists($obj, $nodes[0])){
                return $this->setByArray($obj->{$nodes[0]}, array_slice($nodes, 1), $val);
            }else{
                return false;
            }
        }elseif ($obj===null){
            $obj = [];
            return $this->setByArray($obj, $nodes, $val);
        }else{
            return false;
        }

    }
    public static function isValidPath($path){
        if(strlen($path) == 0){
            return false;
        }
        if(strlen($path) == 1){
            return $path == '$';
        }
        return substr($path,0,2) == '$.';
    }
    private $hookGet;
    private $obj;
}