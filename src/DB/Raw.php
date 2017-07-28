<?php
namespace PhpBoot\DB;
/**
 * 原始sql字符串, 拼接时不进行转义
 * @author caoym
 *
 */
class Raw
{
    /**
     * @param string $str
     */
    function __construct($str) {
        $this->str = $str;
    }
    public function __toString(){
        return $this->str;
    }
    public function get(){
        return $this->str;
    }
    private $str;
}
