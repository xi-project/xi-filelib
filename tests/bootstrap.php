<?php

if (!@include __DIR__ . '/../vendor/autoload.php') {
    die("You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install --dev
");
}

gc_enable();

define('ROOT_TESTS', realpath(__DIR__));

// Autoload test classes
spl_autoload_register(function($class) {
    $filename = str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists(ROOT_TESTS . DIRECTORY_SEPARATOR . $filename)) {
        include_once $filename;
    }
    return class_exists($class, false);
});
