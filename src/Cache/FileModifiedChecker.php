<?php
namespace PhpBoot\Cache;

/**
 * 检查文件是否过期
 * @author caoym
 */
class FileModifiedChecker
{
    /**
     * @param string|array $fileName 文件的绝对路径
     */
    function __construct($fileName){
        $fileNames = array();
        if(is_string($fileName)){
            $fileNames[]=$fileName;
        }else{
            is_array($fileName) or \PhpBoot\abort(new \InvalidArgumentException("string or array is required by param 0"));
            $fileNames = $fileName;
        }
        foreach ($fileNames as $fileName){
            if(is_file($fileName)){
                $this->fileName[$fileName] = @filemtime($fileName);
            }else {
                $this->fileName[$fileName] = @filemtime($fileName);
                if(!is_dir($fileName)){
                    continue;
                }
                $files = @dir($fileName) or \PhpBoot\abort("open dir $fileName failed");
                while (!!($file = $files->read())){
                    if($file == '.' || $file == '..') {
                        continue;
                    }
                    $this->fileName[$fileName.'/'.$file] = @filemtime($fileName.'/'.$file);
                }
                $files->close();
            }
        }
    }
    /**
     * 判断是否过期
     * @param mixed $data 从缓存中得到的数据
     * @param int $createdTime
     * @return boolean
     */
    public function __invoke($data, $createdTime){
        foreach ($this->fileName as $name => $time){
            if(@filemtime($name) != $time){
                return false;
            }
        }
        return true;
    }
    private $fileName=[]; //文件全路径
}
