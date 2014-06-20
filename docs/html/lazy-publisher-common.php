<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\ArbitraryVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Publisher\Linker\ReversibleCreationTimeLinker;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Events as VersionProviderEvents;
use Xi\Filelib\Event\VersionProviderEvent;

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/web/lazy-files', '600', '700', 'lazy-files'),
    new ReversibleCreationTimeLinker()
);
$publisher->attachTo($filelib);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin, array('default'));

$filelib->getEventDispatcher()->addListener(
    VersionProviderEvents::VERSIONS_PROVIDED,
    function(VersionProviderEvent $event) use ($publisher) {
        foreach ($event->getVersions() as $version) {
            $publisher->publishVersion($event->getFile(), Version::get($version));
        }
    }
);

$arbitraryPlugin = new ArbitraryVersionPlugin(
    'arbitrary',
    function () {
        return array('x' => 666);
    },
    function (array $params) {

        if (!is_numeric($params['x'])) {
            return false;
        }

        if ($params['x'] < 200 || $params['x'] > 2000) {
            return false;
        }

        if ($params['x'] % 100) {
            return false;
        }

        return true;
    },
    function (File $file, array $params, ArbitraryVersionPlugin $plugin) {
        return array(
            array('setImageCompression',Imagick::COMPRESSION_JPEG),
            array('setImageFormat', 'jpg'),
            array('setImageCompressionQuality', 50),
            array('cropThumbnailImage', array($params['x'], round($params['x'] / 4))),
            array('sepiaToneImage', 90),
            'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/watermark.png', 'se', 10),
        );
    },
    'image/jpeg'
);
$filelib->addPlugin($arbitraryPlugin);

$versionPlugin = new VersionPlugin(
    array(
        'cinemascope' => array(
            array(
                array('setImageCompression',Imagick::COMPRESSION_JPEG),
                array('setImageFormat', 'jpg'),
                array('setImageCompressionQuality', 50),
                array('cropThumbnailImage', array(800, 200)),
                array('sepiaToneImage', 90),
                'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/watermark.png', 'se', 10),
            ),
            'image/jpeg'
        )
    )
);
$versionPlugin->enableLazyMode();
$filelib->addPlugin($versionPlugin);




