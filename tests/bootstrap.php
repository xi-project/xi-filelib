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

// set_include_path(get_include_path() . DIRECTORY_SEPARATOR . '/wwwroot/librars');

// Autoload test classes
define('ROOT_TESTS', realpath(__DIR__));
spl_autoload_register(function($class) {
    $filename = str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists(ROOT_TESTS . DIRECTORY_SEPARATOR . $filename)) {
        include_once $filename;
    }
    return class_exists($class, false);
});
