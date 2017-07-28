<?php
namespace PhpBoot\Utils;
/**
 * Class AnnotationParams
 */
class AnnotationParams implements \Countable, \ArrayAccess
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
        if($len == 0){
            return;
        }
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
            $this->prePos = strlen($text);
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
                    \PhpBoot\abort('json_decode failed with '.json_last_error_msg(), [$text]);
                }
            }
            return $decoded;
        }
        return $text;
    }
    private $cachedParams = [];
    private $rawParams = [];
    private $prePos = 0;

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->getParam($offset, $this) != $this;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getParam($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        \PhpBoot\abort(new \BadMethodCallException('not impl'));
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        \PhpBoot\abort(new \BadMethodCallException('not impl'));
    }
}
