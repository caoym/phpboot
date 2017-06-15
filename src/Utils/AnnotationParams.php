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
            $this->rawParams[] = $text;
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
            if($state == 'stateNormal' && count($this->rawParams)+1 == $limit){
                break;
            }
            $pos = $this->$state($text, $pos, $state);
            if($pos === false || $pos>= $len){
                break;
            }
        };
        if($this->prePos != strlen($text)){
            $this->rawParams[] = substr($text,$this->prePos);
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
        if(!preg_match('/[\s"\']/', $todo, $found, PREG_OFFSET_CAPTURE) ||
            count($found)==0){
            $this->rawParams[] = substr($text,$this->prePos);
            return false;
        }
        list($chars, $offset) = $found[0];

        if($chars == '"'){
            $next = 'stateDoubleQ';
            return $pos + $offset + 1;
        }
//        elseif ($chars == '\''){
//            $next = 'stateSingleQ';
//            return $pos + $offset + 1;
//        }
        else{
            $this->rawParams[] = substr($text,$this->prePos, $pos-$this->prePos+$offset);
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
//    /**
//     * 进入单引号状态
//     */
//    private function stateSingleQ($text, $pos, &$next){
//
//        $found = [];
//        $todo = substr($text,$pos);
//        if(!preg_match('/[\\\\\']/', $todo, $found, PREG_OFFSET_CAPTURE) ||
//            count($found)==0){
//            return false;
//        }
//        list($chars, $offset) = $found[0];
//        if($chars == '\\'){
//            return $pos+$offset+2;
//        }else{
//            $next = 'stateNormal';
//            return $pos+$offset+1;
//        }
//    }
    /**
     * 进入双引号状态
     */
    private function stateDoubleQ($text, $pos, &$next){

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
        return count($this->rawParams);
    }

    public function getParam($pos, $default = null, $ignoreError=false)
    {
        if(isset($this->cachedParams[$pos])){
            return $this->cachedParams[$pos];
        }
        if(isset($this->rawParams[$pos])){
            $param = $this->rawParams[$pos];
            $param = $this->stripSlashes($param, $ignoreError);
            $this->cachedParams[$pos] = $param;
            return $param;
        }else{
            return $default;
        }
    }

    public function getRawParam($pos, $default = null)
    {
        if(isset($this->rawParams[$pos])){
            return $this->rawParams[$pos];
        }else{
            return $default;
        }
    }

    private function stripSlashes($text, $ignoreError)
    {
        if(strlen($text)>=2 && substr($text,0,1) == '"'){
            $decoded = json_decode($text);
            if(json_last_error()){
                if($ignoreError){
                    return $text;
                }else{
                    fail('json_decode failed with '.json_last_error_msg(), [$text]);
                }
            }
            return $decoded;
        }
        return $text;
    }
    private $cachedParams = [];
    private $rawParams = [];
    private $prePos = 0;

}
