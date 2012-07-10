<?php

if (!@include __DIR__ . '/../vendor/autoload.php') {
    die("You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install --dev
");
}

gc_enable();

// Add Zend Framework 1 to include path and register an autoloader for ZF1 to
// work side by side with ZF2.
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library'),
    get_include_path(),
)));

spl_autoload_register(function($class) {
    $filename = str_replace("_", DIRECTORY_SEPARATOR, $class) . '.php';

    foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
        if (file_exists($includePath . DIRECTORY_SEPARATOR . $filename)) {
            include_once $filename;
            break;
        }
    }

    return class_exists($class, false);
});

define('ROOT_TESTS', realpath(__DIR__));
