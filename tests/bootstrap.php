<?php

use Doctrine\Common\ClassLoader;

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

require ROOT_TESTS . '/vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';

$paths = array(
    'Xi\Filelib'      => ROOT_TESTS . '/../library',
    'Xi\Tests'        => ROOT_TESTS,
    'Zend\Filter'     => ROOT_TESTS . '/vendor/zendframework/filter/php',
    'Zend\Stdlib'     => ROOT_TESTS . '/vendor/zendframework/stdlib/php',
    'Zend\Locale'     => ROOT_TESTS . '/vendor/zendframework/locale/php',
    'Zend'            => ROOT_TESTS . '/vendor/zendframework/registry/php',
    'Doctrine\Common' => ROOT_TESTS . '/vendor/doctrine/common/lib',
    'Doctrine\DBAL'   => ROOT_TESTS . '/vendor/doctrine/dbal/lib',
    'Doctrine\ORM'    => ROOT_TESTS . '/vendor/doctrine/orm/lib',
    'Symfony\Component\HttpFoundation' => ROOT_TESTS . '/vendor/symfony/http-foundation'
);

foreach ($paths as $namespace => $path) {
    $classLoader = new ClassLoader($namespace, $path);
    $classLoader->register();
}

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
