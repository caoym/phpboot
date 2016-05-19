<?php
use caoym\util\IoCFactory;
use caoym\util\ClassLoader;

require_once __DIR__.'/../lib/caoym/AutoLoad.php';
ClassLoader::addInclude(__DIR__.'/apis/');

$factory  = new IoCFactory(__DIR__.'/conf.json');
$router = $factory->create('caoym\\phprs\\RouterWithCache');
$router();