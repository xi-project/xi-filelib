<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Adapter\JsonBackendAdapter;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;

require_once __DIR__ . '/../../vendor/autoload.php';

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/data/private'), new TimeDirectoryIdCalculator()),
    new JsonBackendAdapter(__DIR__ . '/filelib-example.json')
);

// Randomizes the name of the uploaded file every time
$filelib->addPlugin(new RandomizeNamePlugin());
