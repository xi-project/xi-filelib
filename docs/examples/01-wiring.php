<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Linker\SequentialLinker;
use Xi\Filelib\File\FileProfile;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// 01. Wiring

$path = __DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity';
$paths = array(__DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity');

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
    new FilesystemStorage(__DIR__ . '/data/private', new LeveledDirectoryIdCalculator()),
    new DoctrineOrmPlatform($entityManager),
    new SymlinkFilesystemPublisher(__DIR__ . '/data/public'),
    new EventDispatcher()
);

// 02. Profiles & plugins

$filelib->addProfile(new FileProfile('default', new SequentialLinker()));




// 03. Basic operation (shortcuts some day)

$folder = $filelib->getFolderOperator()->findRoot();
$file = $filelib->getFileOperator()->upload(__DIR__ . '/self-lussing-manatee.jpg', $folder);


