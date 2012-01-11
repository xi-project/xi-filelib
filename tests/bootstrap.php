<?php

gc_enable();

/**
 * Maximum level error reporting
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Get both the test and library directories in the include path
 */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library'),
    get_include_path(),
)));

echo get_include_path();

/**
 * Register a trivial autoloader
 */


require SYMFONY2_VENDOR_DIR . '/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Xi\Tests', ROOT_TESTS);
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Zend', SYMFONY2_VENDOR_DIR . '/zend-framework/library');
$classLoader->register();


$classLoader = new \Doctrine\Common\ClassLoader('Xi\Filelib', ROOT_TESTS . '/../library');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', SYMFONY2_VENDOR_DIR . '/doctrine-common/lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', SYMFONY2_VENDOR_DIR . '/doctrine-dbal/lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\ORM', SYMFONY2_VENDOR_DIR . '/doctrine/lib');
$classLoader->register();

spl_autoload_register(function($class) {
    $filename = str_replace(array("\\", "_"), DIRECTORY_SEPARATOR, $class) . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
        if (file_exists($includePath . DIRECTORY_SEPARATOR . $filename)) {
            include_once $filename;
            break;
        }
    }
    return class_exists($class, false);
});
