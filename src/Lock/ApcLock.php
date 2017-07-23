<?php
namespace PhpBoot\Lock;

class ApcLock implements LockInterface
{

    public function lock($key, $ttl)
    {
        if(apc_add($key, 1, $ttl)){
            $this->locked = true;
            return true;
        }else{
            return false;
        }
    }

    public function unlock($key)
    {
        $this->locked or \PhpBoot\abort("unlock unlocked $key");
        $res = apc_delete($key);
        $this->locked = false;
        return $res;
    }
    private $locked=false;
}