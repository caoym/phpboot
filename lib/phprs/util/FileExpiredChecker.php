<?php
namespace phprs\util;

/**
 * 检查文件是否过期
 * @author caoym
 */
class FileExpiredChecker
{
    /**
     * @param string|array $file_name 文件的绝对路径
     */
    function __construct($file_name){
        $file_names = array();
        if(is_string($file_name)){
            $file_names[]=$file_name;
        }else{
            Verify::isTrue(is_array($file_name));
            $file_names = $file_name;
        }
        foreach ($file_names as $file_name){
            if(is_file($file_name)){
                $this->file_name[$file_name] = @filemtime($file_name);
            }else {
                $this->file_name[$file_name] = @filemtime($file_name);
                if(!is_dir($file_name)){
                    continue;
                }
                $files = @dir($file_name);
                Verify::isTrue($files!== null, "open dir $file_name failed");
                while (!!($file = $files->read())){
                    if($file == '.' || $file == '..') {continue;}
                    $this->file_name[$file_name.'/'.$file] = @filemtime($file_name.'/'.$file);
                }
                $files->close();
            }
        }
    }
    /**
     * 判断是否过期
     * @param mixed $data 从缓存中得到的数据
     * @return boolean
     */
    public function __invoke($data, $create_time){
        $res = false;
        foreach ($this->file_name as $name => $time){
            if(@filemtime($name) !== $time){
                Logger::info("cache expired by file $name modified");
                return false;
            }
            $res = true;
        }
        return $res;
    }
    private $file_name=array(); //文件全路径
}
