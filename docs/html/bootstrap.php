<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Adapter\JsonBackendAdapter;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Backend\Cache\Adapter\MemcachedCacheAdapter;

require_once __DIR__ . '/../../vendor/autoload.php';

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/data/private'), new TimeDirectoryIdCalculator()),
    new JsonBackendAdapter(__DIR__ . '/filelib-example.json')
);

/*
// Uncomment me to enable caching
$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);
$filelib->createCacheFromAdapter(
    new Cache(new MemcachedCacheAdapter($memcached))
);
*/

// Randomizes the name of the uploaded file every time
$filelib->addPlugin(new RandomizeNamePlugin());
