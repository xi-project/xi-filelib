<?php

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Backend\Platform\JsonPlatform;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\Cache\Cache;
use Xi\Filelib\Cache\Adapter\MemcachedCacheAdapter;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\ZendSlugifier;
use Xi\Transliterator\IntlTransliterator;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Authorization\Adapter\SimpleAuthorizationAdapter;
use Xi\Filelib\Authorization\AuthorizationPlugin;

require_once __DIR__ . '/../../vendor/autoload.php';

$stopwatch = new Stopwatch();

$ed = new TraceableEventDispatcher(new EventDispatcher(), $stopwatch);

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/data/private'), new TimeDirectoryIdCalculator()),
    new JsonPlatform(__DIR__ . '/filelib-lifecycle.json'),
    $ed
);

$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);
$filelib->setCache(
    new Cache(new MemcachedCacheAdapter($memcached))
);

// Randomizes the name of the uploaded file every time
$filelib->addPlugin(new RandomizeNamePlugin());

$AuthorizationAdapter = new SimpleAuthorizationAdapter();
$AuthorizationPlugin = new AuthorizationPlugin($AuthorizationAdapter);
$filelib->addPlugin($AuthorizationPlugin, array('default'));

$AuthorizationAdapter
    ->setFolderWritable(true)
    ->setFileReadableByAnonymous(false)
    ->setFileReadable(true);

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/web/files', '600', '700', 'files'),
    new BeautifurlLinker(
        new ZendSlugifier(new IntlTransliterator())
    )
);
$publisher->attachTo($filelib);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin);

$versionPlugin = new VersionPlugin(
    'cinemascope',
    array(
        array('setImageCompression',Imagick::COMPRESSION_JPEG),
        array('setImageFormat', 'jpg'),
        array('setImageCompressionQuality', 50),
        array('cropThumbnailImage', array(800, 200)),
        array('sepiaToneImage', 90),
        'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/watermark.png', 'se', 10),
    )
);
$filelib->addPlugin($versionPlugin);

$manateePath = realpath(__DIR__ . '/../../docs/html/manatees/manatus-22.jpg');

$filelib->upload($manateePath);

var_dump($ed->getCalledListeners());
