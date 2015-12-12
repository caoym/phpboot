<?php

/**
 * $Id: index.php 525 2015-11-20 10:46:46Z yangmin.cao $
 * @author caoyangmin
 * @brief 入口
 */
use caoym\util\Logger;
use caoym\util\IoCFactory;
use caoym\util\exceptions\BadRequest;
use caoym\util\exceptions\NotFound;
use caoym\util\ClassLoader;
use caoym\util\exceptions\Forbidden;


ini_set('display_errors', 0);
ini_set('date.timezone', 'Asia/Shanghai');

$project_path = __DIR__;
$lib_path = $project_path.'/../lib/';
require_once $lib_path.'/caoym/AutoLoad.php';
ClassLoader::addInclude($project_path.'/apis/');

//Logger::$writer = Logger::$to_void; //日志不输出

//CLI调试时模拟请求
//$_SERVER['REQUEST_METHOD']='GET';
//$_SERVER['REQUEST_URI'] = '/hw';

//依赖注入工厂, 通过此工厂创建的类自动注入配置中指定的属性(如果有的话)
//设置配置文件的替换字典, 如果配置文件中有用{}符号标记的变量, 配置文件加载时{}将被替换成指定的值
$factory  = new IoCFactory($project_path.'/conf.json', array(
    'lib_path' => $lib_path,
    'app_root' => $project_path,
));

//创建路由器,第二个参数可选,表示用指定的属性列表覆盖配置文件中定义的属性列表
//RouterWithCache创建时自动扫描其属性api_path指定的目录下的所有PHP文件(不扫描子目录),然后创建Router对象
//Router记录路由规则及对应的实现方法,Router将被缓存,当api_path目录下已加载的php文件修改时,缓存失效
//每次处理请求,api的实现类将被重新创建
$router = $factory->create('caoym\\phprs\\RouterWithCache');


$err = null;
$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
//执行请求
try {
    $router();
}catch (NotFound $e) {
    header($protocol . ' 404 Not Found');
    $err = $e;
}catch (BadRequest $e) {
    header($protocol . ' 400 Bad Request');
    $err = $e;
}catch (Forbidden $e){
    header($protocol . ' 403 Forbidden');
    $err = $e;
}
if($err){
    header("Content-Type: application/json; charset=UTF-8");
    $estr = array(
        'error' => get_class($err),
        'message' => $err->getMessage(),
    );
    echo json_encode($estr);
}
