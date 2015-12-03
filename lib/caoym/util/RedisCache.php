<?php
namespace caoym\util;
use caoym\util\KVCatchInterface;

/*class Redis{
    public function connect(){}
    public function set(){}
    public function get(){}
    public function del(){}
}*/

class RedisCache implements KVCatchInterface
{
   
    /**
     * 设置key
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $var, $ttl=null){
        if($this->serialize){
            $var = serialize($var);
        }
        if($ttl === null){
            $this->getImpl()->set($key, $var);
        }else{
            return $this->getImpl()->setex($key, $ttl, $var);
        }
    }
    /**
     * 删除key
     * @param string $key
     * @return boolean
    */
    public function del($key){
        return $this->getImpl()->delete($key) == 1;
    }
    /**
     * get key
     * @param string $key
     * @param boolean $succeeded
     * @return mixed
    */
    public function get($key, &$succeeded=null){
        $res = $this->getImpl()->get($key);
        $succeeded = ($res !== false);
        if($this->serialize){
            $res = unserialize($res);
        }
        return $res;
    }
    private function getImpl(){
        if($this->redis === null){
            $this->redis = new \Redis();
        }
        if(!$this->redis->isConnected()){
            $this->redis->connect($this->host, $this->port);
        }
        return $this->redis;
    }
    /** 
     * @property 
     * 服务器地址
     */
    private $host;
    /**
     * @property
     * 服务器端口
     */
    private $port;
    /**
     * @var \Redis
     */
    private $redis;
    /** @property */
    private $serialize = true;
}
