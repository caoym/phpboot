<?php
/***************************************************************************
 *
* Copyright (c) 2013 . All Rights Reserved
*
**************************************************************************/
/**
 * $Id: SaftyFileWriter.php 57435 2014-12-21 15:04:22Z caoyangmin $
 * @author caoyangmin(caoyangmin@baidu.com)
 * @brief
 */

namespace caoym\util;
/**
 * 并发安全的写文件
 * 其原子性取决于文件系统
 * 通过先写临时文件, 然后重命名的方式实现
 * @author caoym
 *
 */
class SaftyFileWriter
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
        $pos = strrpos ($path, '/');
        $file_name="";
        $file_dir="";
        if($pos === false){
            $file_name=$path;
        }else{
            $file_dir = substr($path, 0,$pos+1);
            $file_name = substr($path, $pos+1);
        }
        $tmp_file= tempnam($file_dir, 'tsb_sfw');
        Verify::isTrue(false !== file_put_contents($tmp_file, $data), "write to file: $tmp_file failed");
        if($overwrite){
            @unlink($path); //删除原始文件
        }
        if(!@rename($tmp_file, $path)){
            @unlink($tmp_file); //删除原始文件
            Verify::e("write to file: $tmp_file failed");
            return false;
        }
        return true;
    }
}
