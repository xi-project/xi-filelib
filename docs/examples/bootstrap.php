<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Platform\JsonPlatform;
use Xi\Filelib\Storage\FilesystemStorage;
use Doctrine\ORM\Tools\Setup;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;

require_once __DIR__ . '/../../vendor/autoload.php';

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/data/private'), new TimeDirectoryIdCalculator()),
    new JsonPlatform(__DIR__ . '/../filelib-example.json')
);

$filelib->addProfile(new FileProfile('default'));
$filelib->addPlugin(new RandomizeNamePlugin(), ['default']);
