<?php
namespace phprs\util;

class FileOp
{
    /**
     * 复制文件或目录
     * @param string $src
     * @param string $dst
     * @param string $except
     * @return boolean
     */
    static public function copys($src, $dst, $except=null) {
        if (is_file($src)) {
            if ($except !== null && (realpath($except) == realpath($src) || realpath($except) == realpath($dst))) {
                return true;
            }
            return @copy($src, $dst);
        }
        Verify::isTrue(is_dir($src), "$src not a file nor a dir");
        
        $dir = dir($src);
        @mkdir($dst);
        while(false !== ( $file = $dir->read()) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $src_path = $src . '/' . $file;
                $dst_path = $dst . '/' . $file;
                if(!self::copys($src_path, $dst_path)){
                    $dir->close();
                    return false;
                }
                Logger::debug("file copy from $src_path to $dst_path");
            }
        }
        $dir->close();
        return true;
    }
    /**
     * mkdir -p
     * @param string $path
     * @param number $mode
     * @return boolean
     */
    static public function mkdirs($path, $mode = 0777) {
        $dirs = explode('/', $path);
        $count = count($dirs);
        $path = '';
        for ($i = 0; $i < $count; ++ $i) {
            if ($i !== 0) {
                $path .= DIRECTORY_SEPARATOR;
            }
            if ($dirs[$i] === '') {
                continue;
            }
            $path .= $dirs[$i];
            if (! is_dir($path) && ! mkdir($path, $mode)) {
                return false;
            }
        }
        return true;
    }
    /**
     * 临时目录
     * @param string $dir
     * @param string $prefix
     * @return string
     */
    function tempdir($dir, $prefix) {
        $tempfile=tempnam($dir, $prefix);
        if (file_exists($tempfile)) { 
            unlink($tempfile); 
        }
        mkdir($tempfile);
        return $tempfile;
    }
}
