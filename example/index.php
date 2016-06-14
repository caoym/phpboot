<?php
use phprs\util\IoCFactory;
use phprs\util\ClassLoader;

require_once __DIR__.'/../lib/phprs/AutoLoad.php';
ClassLoader::addInclude(__DIR__.'/apis/');

$factory  = new IoCFactory(__DIR__.'/conf.json');
$router = $factory->create('phprs\\RouterWithCache');
$router();