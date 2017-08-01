<?php
namespace  PhpBoot\Cache;
use Doctrine\Common\Cache\Cache;

/**
 * 可检查缓存是否失效的缓存
 * @author caoym
 *
 */
class CheckableCache
{
    function __construct(Cache $impl){
        $this->impl = $impl;
    }
   
    /**
     * 设置cache
     *
     * @param string $name
     * @param mixed $var
     * @param int
     * @param callable $expireCheck
     * @return boolean
     * 缓存过期检查方法, 缓存过期(超过ttl)后, get时调用, 返回true表示缓存继续可用.
     * 如checker($got_var, $time)
     *
     */
    public function set($name, $var, $ttl = 0, callable $expireCheck = null)
    {
        $res = $this->impl->save($name, serialize(array(
            $var,
            $ttl,
            $expireCheck,
            time(),
        )), is_null($expireCheck) ? $ttl : 0);
        return $res;
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @param mixed|null $expiredData
     * @param bool $deleteExpiredData
     * @return mixed
     */
    public function get($name, $default = null, &$expiredData=null, $deleteExpiredData=true)
    {
        $expiredData = null;
        $res = $this->impl->fetch($name);
        if ($res) {
            list ($data, $ttl, $checker, $createdTime) = unserialize($res);
            // 如果指定了checker, ttl代表每次检查的间隔时间, 0表示每次get都需要经过checker检查
            // 如果没有指定checker, ttl表示缓存过期时间, 为0表示永不过期
            if ($checker !== null) {
                if ($ttl == 0 || ($createdTime + $ttl < time())) {
                    $valid = $checker($data, $createdTime);
                    if(!$valid){
                        $expiredData = $data;
                        if($deleteExpiredData){
                            $this->impl->delete($name);
                        }
                        return $default;
                    }
                    
                }
            }else if ($ttl != 0 && ($createdTime + $ttl < time())) {
                $this->impl->delete($name);
                return $default;
            }
            return $data;
        }
        return $default;
    }
    /**
     * 删除
     * @param string $name
     */
    public function del($name){
        return  $this->impl->delete($name);
    }
    public function getImpl()
    {
        return $this->impl;
    }
    /**
     * @var Cache
     */
    private $impl;
}
