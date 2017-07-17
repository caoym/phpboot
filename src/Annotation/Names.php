<?php
namespace PhpBoot\Annotation;

if(!defined('PHPBOOT_ANNOTATION_VLD')){
    define('PHPBOOT_ANNOTATION_VLD', 'vld');
}
if(!defined('PHPBOOT_ANNOTATION_ROUTE')){
    define('PHPBOOT_ANNOTATION_ROUTE', 'route');
}
if(!defined('PHPBOOT_ANNOTATION_PATH')){
    define('PHPBOOT_ANNOTATION_PATH', 'path');
}

class Names{
    const VLD = PHPBOOT_ANNOTATION_VLD;
    const ROUTE = PHPBOOT_ANNOTATION_ROUTE;
    const PATH = PHPBOOT_ANNOTATION_PATH;
}
