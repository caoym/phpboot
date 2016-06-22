<?php
namespace phprs;
use phprs\util\IoCFactory;

class Bootstrap
{
    static public function run($conf_file) {
        require_once __DIR__.'/AutoLoad.php';
        $factory  = new IoCFactory($conf_file);
        $router = $factory->create('phprs\\RouterWithCache');
        $router();
    }
}
