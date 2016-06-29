<?php
use phprs\Bootstrap;
use phprs\util\Logger;

//ini_set('display_errors', 0);

// ** if using composer, disable the following line **
require_once __DIR__.'/../../lib/phprs/AutoLoad.php';

// ** if using composer, enable the following line **
//require_once __DIR__."/../vendor/autoload.php";

//set log flag
//Logger::$flags = Logger::WARNING|Logger::DEBUG|Logger::ERROR|Logger::INFO;

//set log output
//Logger::$writer = Logger::$to_echo;

//simulate request in CLI
//$_SERVER['REQUEST_URI'] = '/api/apis';
//$_SERVER['REQUEST_METHOD'] = 'GET';

Bootstrap::run(__DIR__.'/../conf.php');