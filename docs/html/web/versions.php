<?php

use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\CreationTimeLinker;
use Xi\Filelib\Publisher\Publisher;

require_once __DIR__ . '/../bootstrap.php';

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/files', '600', '700', 'files'),
    new CreationTimeLinker()
);
$publisher->attachTo($filelib);

/*
thumb:
identifier: thumb
            type: Xi\Filelib\Plugin\Image\VersionPlugin
            profiles: [versioned]
            imageMagickOptions:
                ImageCompression: '8'
                ImageFormat: jpg
                ImageCompressionQuality: 50
            extension: jpg
            commands:
                scale:
                    type: Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand
                    method: scaleImage
                    parameters: ['640', '480', true]
                watermark:
                    type: Xi\Filelib\Plugin\Image\Command\WatermarkCommand
                    WaterMarkImage: %kernel.root_dir%/data/watermark.png
                    WaterMarkPosition: se
                    WaterMarkPadding: 10

scale:
                    type: Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand
                    method: cropThumbnailImage
                    parameters: [800, 200]
                sepia:
                    type: Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand
                    method: sepiaToneImage
                    parameters: [ 90 ]
*/


$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin, array('default'));

$versionPlugin = new VersionPlugin(
    array(
        'cinemascope' => array(
            array(
                array('setImageCompression',Imagick::COMPRESSION_JPEG),
                array('setImageFormat', 'jpg'),
                array('setImageCompressionQuality', 50),
                array('cropThumbnailImage', array(800, 200)),
                array('sepiaToneImage', 90),
                'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/../watermark.png', 'se', 10),
            ),
            'image/jpeg'
        )
    )
);
$filelib->addPlugin($versionPlugin, array('default'));

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-12.jpg');
$publisher->publishAllVersions($file);

?>

<html>
    <head>
        <title>Mighty manatee</title>
    </head>
    <body>
        <h1>You just published a picture of a manatee and a cinemascope thumbnail. Aww!!!</h1>
        <p>
            <img src="<?php echo $publisher->getUrl($file, 'original'); ?>" />
        </p>

        <p>
            <img src="<?php echo $publisher->getUrl($file, 'cinemascope'); ?>" />
        </p>

    </body>
</html>
