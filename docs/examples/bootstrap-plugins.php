<?php

use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;
use Xi\Filelib\Plugin\Image\Command\WatermarkCommand;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;
use Xi\Filelib\Plugin\AutomaticPublisherPlugin;

// 03. Plugins

// @todo: refactor to singular versionplugin with many versions

$filelib->addPlugin(
    new VersionPlugin(
        'thumbster',
        $filelib->getTempDir(),
        'jpg',
        array(
            'imageMagickOptions' => array(
                'imageCompression' => 8,
                'imageFormat' => 'jpg',
                'imageCompressionQuality' => 50
            ),
            'commands' => array(
                'scale' => new ExecuteMethodCommand('scaleImage', array(640, 480, true)),
                'watermark' => new WatermarkCommand(__DIR__ . '/watermark.png', 'se', 10),
            )
        )
    ),
    array('default')
);

$filelib->addPlugin(
    new VersionPlugin(
        'ribuls',
        $filelib->getTempDir(),
        'jpg',
        array(
            'imageMagickOptions' => array(
                'imageCompression' => 8,
                'imageFormat' => 'jpg',
                'imageCompressionQuality' => 10
            ),
            'commands' => array(
                'scale' => new ExecuteMethodCommand('scaleImage', array(400, 300, false)),
                'watermark' => new WatermarkCommand(__DIR__ . '/watermark.png', 'se', 10),
            )
        )
    ),
    array('default')
);

$publisher = new SymlinkFilesystemPublisher(
    $filelib,
    realpath(__DIR__ . '/web/files'),
    0600,
    0700,
    '/files'
);

$filelib->addPlugin(
    new AutomaticPublisherPlugin($publisher)
);
