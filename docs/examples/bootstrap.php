<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Plugin\RandomizeNamePlugin;

require_once __DIR__ . '/../../vendor/autoload.php';

// Basic Wiring

$path = __DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity';

$paths = array(
    __DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity'
);

$isDevMode = true;

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'user'     => 'root',
    'password' => 'g04753m135',
    'dbname'   => 'filelib_example',
);
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/data/private')),
    new DoctrineOrmPlatform($entityManager)
);

// Add a default profile with the simplest sequential linker possible

$filelib->addProfile(new FileProfile('default'));
$filelib->addPlugin(new RandomizeNamePlugin(), array('default'));
