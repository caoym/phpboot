<?php
namespace PhpBoot\Utils;

/**
 * Class AnnotationParams
 */
class AnnotationParams
{

    public function __construct($text, $limit)
    {
        if($limit == 1){
            $this->substr[] = $text;
            return;
        }
        if($limit <= 0){
            return;
        }
        $text = ltrim($text);
        $pos = 0;
        $state = 'stateNormal';
        $len = strlen($text);
        while (true){
            if($state == 'stateNormal' && count($this->substr)+1 == $limit){
                break;
            }
            $pos = $this->$state($text, $pos, $state);
            if($pos === false || $pos>= $len){
                break;
            }
        };
        if($this->prePos != strlen($text)){
            $this->substr[] = substr($text,$this->prePos);
        }
    }

    /**
     * 普通状态
     */
    private function stateNormal($text, $pos, &$next)
    {
        //查找引号或者空格
        $found = [];
        $todo = substr($text,$pos);
        if(!preg_match('/[\s"]/', $todo, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            $this->substr[] = substr($text,$this->prePos);
            return false;
        }
        list($chars, $offset) = $found[0];

        if($chars == '"'){
            $next = 'stateQ';
            return $pos + $offset + 1;
        }else{
            $this->substr[] = substr($text,$this->prePos, $pos-$this->prePos+$offset);
            $next = 'stateSpace';
            $this->prePos = $pos + $offset + 1;
            return $this->prePos;
        }

    }
    /**
     * 进入空格状态
     */
    private function stateSpace($text, $pos, &$next)
    {
        $found = [];
        $todo = substr($text,$pos);
        if(!preg_match('/\S/', $todo, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            return false;
        }
        list($chars, $offset) = $found[0];
        $this->prePos = $pos + $offset;
        $next = 'stateNormal';
        return $this->prePos;
    }
    /**
     * 进入引号状态
     */
    private function stateQ($text, $pos, &$next){

        $found = [];
        $todo = substr($text,$pos);
        if(!preg_match('/[\\\\"]/', $todo, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            return false;
        }
        list($chars, $offset) = $found[0];
        if($chars == '\\'){
            return $pos+$offset+2;
        }else{
            $next = 'stateNormal';
            return $pos+$offset+1;
        }
    }

    public function count()
    {
        return count($this->substr);
    }

    public function getParam($pos, $default = null)
    {
        if(isset($this->substr[$pos])){
            return $this->substr[$pos];
        }else{
            return $default;
        }
    }

    public function getRawParam($pos, $default = null)
    {
        if(isset($this->substr[$pos])){
            return $this->substr[$pos];
        }else{
            return $default;
        }
    }
    private $substr = [];
    private $prePos = 0;

}
