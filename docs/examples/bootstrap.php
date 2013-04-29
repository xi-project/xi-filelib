<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Xi\Filelib\Acl\SimpleAcl;
use Xi\Filelib\Linker\SequentialLinker;
use Xi\Filelib\File\FileProfile;

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
    new FilesystemStorage(realpath(__DIR__ . '/data/private'), new LeveledDirectoryIdCalculator()),
    new DoctrineOrmPlatform($entityManager),
    new SymlinkFilesystemPublisher(realpath(__DIR__ . '/web/files'), 0600, 0700, '/files'),
    new EventDispatcher()
);
$filelib->setTempDir(__DIR__ . '/data/temp');

// Setting ACL is not mandatory. Simple is used by default.

$filelib->setAcl(new SimpleAcl(true));

// Add a default profile with the simplest sequential linker possible

$filelib->addProfile(new FileProfile('default', new SequentialLinker()));


