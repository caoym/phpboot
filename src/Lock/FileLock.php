<?php
namespace PhpBoot\Lock;

use PhpBoot\Utils\SafeFileWriter;

class FileLock implements LockInterface
{

    public function lock($key, $ttl)
    {
        $path = sys_get_temp_dir().'/lock_252a8fdc9b944af99a9bc53d2aea08f1/'.$key;
        $tmpFile = tempnam($path, 'lock');
        return SafeFileWriter::write($tmpFile, json_encode(['time'=>time(), 'ttl'=>$ttl]), false);
    }

    public function unlock($key)
    {
        $path = sys_get_temp_dir().'/lock_252a8fdc9b944af99a9bc53d2aea08f1/'.$key;
        return @unlink($path);
    }
}