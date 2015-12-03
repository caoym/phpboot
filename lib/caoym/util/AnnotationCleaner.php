<?php
namespace caoym\util;

/**
 * 去除//注释的内容，但会跳过引号内的//
 * @author caoym
 *
 */
class AnnotationCleaner
{
    
    static public function clean($text) {
        $o = new AnnotationCleaner();
        return $o->clean_($text);
    }
    //去掉注释
    private function clean_($text) {
        
        $this->dest = '';
        $this->tmp = $text;
        $state = 'stateNormal';
        while ($state){
            $state = $this->$state();
        }
        return $this->dest;
    }
  
    private function stateNormal(){
        $stateBegin = [
            '//'=>'stateAntSL', //单行注释
            '/*'=>'stateAntML',//多行注释
            '\''=>'stateStrSQ', //单引号
            '"'=>'stateStrDQ',//双引号
        ];
        
        $count = strlen($this->tmp);
        for($i=0; $i<$count; $i++){
            foreach ($stateBegin as $k=>$v){
                if(substr($this->tmp, $i, strlen($k)) == $k){
                    $this->dest .= substr($this->tmp, 0, $i);
                    $this->tmp = substr($this->tmp, $i);
                    return $v;
                }
            }
        }
        $this->dest .= $this->tmp;
        $this->tmp = '';
        return false;
    }
    /**
     * 单行注释
     */
    private function stateAntSL(){
        $pos = strpos($this->tmp, "\n");
        if($pos){
            $this->tmp = substr($this->tmp, $pos);
        }else{
            $this->tmp = '';
        }
        return 'stateNormal';
    }
    /**
     * 双行注释
     */
    private function stateAntML(){
        $pos = strpos($this->tmp, "*/");
        if($pos){
            $this->tmp = substr($this->tmp, $pos+2);
        }else{
            $this->tmp = '';
        }
        return 'stateNormal';
    }
    // 单引号
    private function stateStrSQ(){
        return $this->stateStr('\'');
    }
    // 双引号
    private function stateStrDQ(){
        return $this->stateStr('"');
    }
    
    private function stateStr($q){
        $count = strlen($this->tmp);
        for($i=1; $i<$count; $i++){
            if(substr($this->tmp, $i, 1) == $q){
                $this->dest .= substr($this->tmp, 0, $i+1);
                $this->tmp = substr($this->tmp, $i+1);
                return 'stateNormal';
            }else  if(substr($this->tmp, $i, 1) == '\\'){//文本内转义
                $i++;
                continue;//跳过一个
            }
        }
        $this->dest .= $this->tmp;
        $this->tmp = '';
        return 'stateNormal';
    }
    private $tmp;
    private $dest;
}
