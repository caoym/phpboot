<?php

namespace PhpBoot\DB;

/**
 * 剪出字符串中的嵌套字符串
 * 既从aaa"bb\"b"ccc中, 取出"bb\"b"
 * @author caoym
 */
class NestedStringCut{
    
    public function __construct($str){
        
        $pos = 0;
        $state = 'stateNormal';
        while (true){
            $pos = $this->$state($str, $pos, $state);
            if($pos === false){
                break;
            }
        };
        return false;
    }
    
    public function  getSnippets(){
        return $this->snippets;
    }
    
    public function  getText(){
        return  implode('', $this->snippets);
    }
    /**
     * 将剪切后的字符串位置转换成原始字符串位置
     * @param int $pos 
     * @param int
     */
    public function mapPos($pos){
       
        foreach ($this->snippets as $k => $v){
            $pos += $k;
            if($pos < $k + strlen($v)){
                break;
            }
            $pos -= ($k + strlen($v));
            
        }
        return $pos;
    }
    /**
     * 普通状态
     */
    private function stateNormal($str, $pos, &$next){
        $ori = $pos;
        $posSQ = strpos($str, '\'', $pos);
        $posDQ = strpos($str, '"', $pos);
        $pos = $posSQ;
        $this->subStateQ = '\'';
        $next = 'stateQ';
        if($posDQ !== false && (($posDQ < $pos) || ($pos === false)) ){
            $pos = $posDQ;
            $this->subStateQ = '"';
        }
        if($pos !== false){
            $this->snippets[$ori] = substr($str, $ori, $pos-$ori);
            $pos ++;
        }else{
            $this->snippets[$ori] = substr($str, $ori);
        }
        return $pos;
    }
    
    /**
     * 进入引号状态
     */
    private function stateQ($str, $pos, &$next){
        $posESC = strpos($str, '\\', $pos);
        $posQ = strpos($str, $this->subStateQ, $pos);
        $pos = $posESC;
        $next = 'stateESC';
        
        if($posQ !== false && (($posQ<$posESC) || ($posESC === false))){
            $pos = $posQ;
            $next = 'stateNormal';
        }
        if($pos !== false){
            $pos ++;
        }
        return $pos;
    }
    /**
     * 进入转义状态
     */
    private function stateESC($str, $pos, &$next){
        $pos++;
        if($pos >= strlen($str)){
            return false;
        }
        $next = 'stateQ';
        return $pos;
    }
    /**
     * 去掉嵌套字符串后的内容
     * @var array
     */
    private $snippets=array();
    
    private $subStateQ;
}
