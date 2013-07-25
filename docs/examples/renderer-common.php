<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\ZendSlugifier;
use Xi\Transliterator\IntlTransliterator;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Authorization\Adapter\SimpleAuthorizationAdapter;
use Xi\Filelib\Authorization\AuthorizationPlugin;

$AuthorizationAdapter = new SimpleAuthorizationAdapter();
$AuthorizationPlugin = new AuthorizationPlugin($AuthorizationAdapter);
$filelib->addPlugin($AuthorizationPlugin, array('default'));

$AuthorizationAdapter
    ->setFolderWritable(true)
    ->setFileReadableByAnonymous(false)
    ->setFileReadable(true);

$publisher = new Publisher(
    $filelib,
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/web/files', '600', '700', 'files'),
    new BeautifurlLinker(
        $filelib,
        new ZendSlugifier(new IntlTransliterator())
    )
);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin, array('default'));

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
$filelib->addPlugin($versionPlugin, array('default'));
