<?php
namespace PhpBoot\Utils;
/**
 * 并发安全的写文件
 * 其原子性取决于文件系统
 * 通过先写临时文件, 然后重命名的方式实现
 * @author caoym
 *
 */
class SafeFileWriter
{
    /**
     * 写入文件
     * @param string $path 路径
     * @param mixed $data 写入的值
     * @param boolean $overwrite 是否覆盖已有文件
     * @return boolean
     */
    static public function write($path, $data, $overwrite = true){
        $path = str_replace('\\', '/', $path);
        $fileDir = dirname($path);
        $tmpFile = tempnam($fileDir);
        false !== @file_put_contents($tmpFile, $data) or \PhpBoot\abort("write to file: $tmpFile failed");
        if($overwrite){
            @unlink($path); //删除原始文件
        }
        if(!@rename($tmpFile, $path)){
            @unlink($tmpFile); //删除原始文件
            \PhpBoot\abort("write to file: $tmpFile failed");
            return false;
        }
        return true;
    }
}
