<?php
namespace PhpBoot\Annotation;

if(!defined('PHPBOOT_ANNOTATION_VALIDATE')){
    define('PHPBOOT_ANNOTATION_VALIDATE', 'v');
}
if(!defined('PHPBOOT_ANNOTATION_ROUTE')){
    define('PHPBOOT_ANNOTATION_ROUTE', 'route');
}
if(!defined('PHPBOOT_ANNOTATION_PATH')){
    define('PHPBOOT_ANNOTATION_PATH', 'path');
}
if(!defined('PHPBOOT_ANNOTATION_BIND')){
    define('PHPBOOT_ANNOTATION_BIND', 'bind');
}
if(!defined('PHPBOOT_ANNOTATION_HOOK')){
    define('PHPBOOT_ANNOTATION_HOOK', 'hook');
}
if(!defined('PHPBOOT_ANNOTATION_INJECT')){
    define('PHPBOOT_ANNOTATION_INJECT', 'inject');
}
class Names{
    const VALIDATE = PHPBOOT_ANNOTATION_VALIDATE;
    const ROUTE = PHPBOOT_ANNOTATION_ROUTE;
    const PATH = PHPBOOT_ANNOTATION_PATH;
    const BIND = PHPBOOT_ANNOTATION_BIND;
    const HOOK = PHPBOOT_ANNOTATION_HOOK;
    const INJECT = PHPBOOT_ANNOTATION_INJECT;
}
