<?php
namespace PhpBoot\Lock;

use PhpBoot\Utils\SafeFileWriter;

class FileLock implements LockInterface
{

    public function lock($key, $ttl)
    {
        $path = sys_get_temp_dir().'/lock_252a8fdc9b944af99a9bc53d2aea08f1/'.$key;
        $tmpFile = tempnam($path, 'lock');
        if(SafeFileWriter::write($tmpFile, json_encode(['time'=>time(), 'ttl'=>$ttl]), false)){
            $this->locked = true;
            return true;
        }else{
            return false;
        }
    }

    public function unlock($key)
    {
        $this->locked or \PhpBoot\abort("unlock unlocked $key");
        $path = sys_get_temp_dir().'/lock_252a8fdc9b944af99a9bc53d2aea08f1/'.$key;
        $res = @unlink($path);
        $this->locked = false;
        return $res;
    }
    private $locked = false;
}