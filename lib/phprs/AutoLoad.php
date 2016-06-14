<?php

/**
 * $Id: AutoLoad.php 56761 2014-12-08 05:17:37Z caoyangmin $
 * @author caoyangmin(caoyangmin@gmail.com)
 * @brief AutoLoad
 */
namespace phprs;
require __DIR__.'/util/ClassLoader.php';
require __DIR__.'/util/AutoClassLoader.php';
use phprs\util\ClassLoader;
ClassLoader::addInclude(dirname(__DIR__));
spl_autoload_register(array(__NAMESPACE__.'\util\ClassLoader', 'autoLoad'));
