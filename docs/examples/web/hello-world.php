<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Plugin\RandomizeNamePlugin;

require_once __DIR__ . '/../../../vendor/autoload.php';

$paths = array(
    __DIR__ . '/../../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity'
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
    new FilesystemStorage(realpath(__DIR__ . '/../data/private'), new LeveledDirectoryIdCalculator()),
    new DoctrineOrmPlatform($entityManager)
);

// Add a default profile
$filelib->addProfile(new FileProfile('default'));

$filelib->addPlugin(new RandomizeNamePlugin(), ['default']);

$file = $filelib->upload(__DIR__ . '/../manatees/manatus-02.jpg');



header("Content-Type: " . $file->getMimetype());
echo file_get_contents($filelib->getStorage()->retrieve($file->getResource()));

