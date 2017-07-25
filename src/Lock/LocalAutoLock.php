<?php
namespace PhpBoot\Lock;

/**
 * 自动加锁
 *
 * 使用
 */
class LocalAutoLock
{
    static function lock($key, $seconds, callable $success, callable $error=null){
        $key = 'lock:'.$key;
        if(function_exists('apc_add')){
            $lock = new ApcLock();
        }else{
            $lock = new FileLock();
        }
        try{
            if(!isset(self::$currentLock[$key])){
                self::$currentLock[$key] = 0;
            }
            if(self::$currentLock[$key] == 0){  //未加锁
                if(!$lock->lock($key, $seconds)){ //加锁失败
                    if($error){
                        return $error();
                    }
                    return;
                }
            }
            //嵌套加锁
            self::$currentLock[$key]++;
        }catch (\Exception $e){
            return $error($e);
        }
        $res = null;
        try{
            $res = $success();
        }catch (\Exception $e){
            self::$currentLock[$key]--;
            if(self::$currentLock[$key] == 0){
                try{
                    $lock->unlock($key);
                }catch (\Exception $e){

                }
            }
            throw $e;
        }
        self::$currentLock[$key]--;
        if(self::$currentLock[$key] == 0){
            try{
                $lock->unlock($key);
            }catch (\Exception $e){

            }
        }
        return $res;
    }

    private $cache=[];
    static private $currentLock=[];
}