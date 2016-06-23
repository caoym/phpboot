<?php
namespace phprs;
use phprs\util\IoCFactory;

class Bootstrap
{
    static public function run($conf_file) {
		// support for CORS （跨域请求)
		if(!empty($_SERVER['HTTP_ORIGIN'])) {
			header('Access-Control-Allow-Origin:*');
			if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
				header('HTTP/1.1 200 ok');
				header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
				header('Access-Control-Allow-Headers:accept, Authorization,access-control-allow-origin');
				header('Access-Control-Max-Age: 86400');
				exit();
			}
		}
        require_once __DIR__.'/AutoLoad.php';
        $factory  = new IoCFactory($conf_file);
        $router = $factory->create('phprs\\RouterWithCache');
        $router();
    }
}
