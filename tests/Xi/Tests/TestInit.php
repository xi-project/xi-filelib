<?php

set_include_path(get_include_path() . ':' . realpath(__DIR__ . "/../../../library"));

define('ROOT_TESTS', realpath(__DIR__ . '/../..'));

require_once "../../../library/Doctrine/Common/ClassLoader.php";

require_once "../../../library/Zend/Loader/Autoloader.php";

Zend_Loader_Autoloader::getInstance();

$classLoader = new \Doctrine\Common\ClassLoader('Xi\Tests', __DIR__ . "/../..");
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Xi', __DIR__ . '/../../../library');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
$classLoader->register();

