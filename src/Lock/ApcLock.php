<?php
namespace PhpBoot\Lock;

class ApcLock implements LockInterface
{

    public function lock($key, $ttl)
    {
        if(apc_add($key, 1, $ttl)){
            return true;
        }else{
            return false;
        }
    }

    public function unlock($key)
    {
        return apc_delete($key);
    }
}