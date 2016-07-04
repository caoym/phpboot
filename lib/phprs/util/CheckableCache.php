<?php
namespace phprs\util;
/**
 * 可检查缓存是否失效的缓存
 * @author caoym
 *
 */
class CheckableCache
{
    /**
     * @param object $impl instanceof of KVCatchInterface
     */
    function __construct($impl, $tag = ''){
        $this->impl = $impl;
        $this->tag = $tag; 
    }
   
    /**
     * 设置cache
     *
     * @param string $name
     * @param mixed $var
     * @param int
     * @param SerializableFunc $expire_check
     * @return boolean
     * 缓存过期检查方法, 缓存过期(超过ttl)后, get时调用, 返回true表示缓存继续可用.
     * 如checker($got_var, $time)
     *
     */
    public function set($name, $var, $ttl = 0, $expire_check = null)
    {
        $name = $this->tag.$name;
        $res = $this->impl->set($name, array(
            $var,
            $ttl,
            $expire_check,
            time(),
        ), is_null($expire_check) ? $ttl : 0);
        if (!$res){
            Logger::warning("set cache $name failed");
        }else{
            Logger::debug("set cache $name ok, ttl=$ttl, check=".($expire_check===null?'null':get_class($expire_check)));
        }
        return $res;
    }
    
    /**
     * 获取cache
     * @param string $name
     * @param boolean $succeeded
     * @return mixed
     */
    public function get($name, &$succeeded=null)
    {
        $name = $this->tag.$name;
        $res = $this->impl->get($name, $succeeded);
        if ($succeeded) {
            $succeeded = false;
            list ($data, $ttl, $checker, $create_time) = $res;
            // 如果指定了checker, ttl代表每次检查的间隔时间, 0表示每次get都需要经过checker检查
            // 如果没有指定checker, ttl表示缓存过期时间, 为0表示永不过期
            if ($checker !== null) {
                if ($ttl == 0 || ($create_time + $ttl < time())) {
                    $valid = false;
                    try{
                        if(is_callable($checker)){
                            $valid = $checker($data, $create_time);
                        }
                    }
                    catch (\Exception $e){
                        Logger::warning('call checker failed with '.$e->getTraceAsString());
                        $valid = false;
                    }
                    if(!$valid){
                        Logger::debug("cache $name expired by checker");
                        $this->impl->del($name);
                        return null;
                    }
                    
                }
            }else if ($ttl != 0 && ($create_time + $ttl < time())) {
                Logger::debug("cache $name expired by ttl");
                $this->impl->del($name);
                return null;
            }
            Logger::debug("get $name from cache, ttl=$ttl, create_time=$create_time, check=".($checker===null?'null':get_class($checker)));
            $succeeded = true;
            return $data;
        }
        return null;
    }
    /**
     * 删除
     * @param string $name
     */
    public function del($name){
        $name = $this->tag.$name;
        return  $this->impl->del($name);
    }
    private $tag;
    private $impl;
}
