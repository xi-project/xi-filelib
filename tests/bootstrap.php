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

define('ROOT_TESTS', realpath(__DIR__));

// Fucktored to use dem autoloader created by da composer
require ROOT_TESTS . '/../vendor/autoload.php';

/**
 * Register a trivial autoloader
 */


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
