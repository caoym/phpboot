<?php
namespace PhpBoot\Lock;

interface LockInterface
{
    public function lock($key, $ttl);
    public function unlock($key);
}