<?php
/**
 * $Id: Native.php 131 2015-10-10 02:25:57Z yangmin.cao $
 * @author caoym(caoyangmin@gmail.com)
 */
namespace caoym\ezsql;
/**
 * 原始sql字符串, 拼接时不进行转义
 * @author caoym
 *
 */
class Native
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
