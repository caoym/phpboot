<?php
namespace PhpBoot\Lock;

use PhpBoot\Utils\SafeFileWriter;

class FileLock implements LockInterface
{
    private $file;
    public function lock($key, $ttl)
    {
        if($this->locked){
            \PhpBoot\abort("relock $key");
        }
        $path = sys_get_temp_dir().'/lock_252a8fdc9b944af99a9bc53d2aea08f1_'.$key;
        $this->file = @fopen($path, 'a');
        if (!$this->file || !flock($this->file, LOCK_EX | LOCK_NB)) {
            if($this->file){
                fclose($this->file);
            }
            return false;
        } else {
            $this->locked = true;
        }
        return true;
    }

    public function unlock($key)
    {
        $this->locked or \PhpBoot\abort("unlock unlocked $key");
        flock($this->file, LOCK_UN);
        fclose($this->file);
        $this->file = null;
        $this->locked = false;
        return true;
    }
    private $locked = false;
}