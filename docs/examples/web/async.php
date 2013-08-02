<?php

use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\EnqueueableCommand;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';

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
        'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/../watermark.png', 'se', 10),
    )
);
$filelib->addPlugin($versionPlugin);

$thumbPlugin = new VersionPlugin(
    'thumb',
    array(
        array('setImageCompression',Imagick::COMPRESSION_JPEG),
        array('setImageFormat', 'jpg'),
        array('setImageCompressionQuality', 30),
        array('scaleImage', array(640, 480, 1)),
        'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/../watermark.png', 'nw', 10),
    )
);
$filelib->addPlugin($thumbPlugin);

$diterator = new DirectoryIterator(__DIR__ . '/../manatees');
$uploaders = array();

for ($x = 1; $x <= 10; $x++) {
    foreach ($diterator as $file) {
        if ($file->isFile()) {

            $filelib->getFileOperator()->setCommandStrategy(
                FileOperator::COMMAND_AFTERUPLOAD,
                EnqueueableCommand::STRATEGY_ASYNCHRONOUS
            );

            $filelib->getFileOperator()->upload($file->getRealPath());

            $uploaders[] = $file->getRealPath();
        }
    }
}

?>

<html>
    <head>
        <title>Mighty manatee</title>
    </head>
    <body>
        <h1>Asynchronous processing</h1>

        <p>
        <?php echo count($uploaders); ?> images have just been uploaded and queued for post processing.
            Run <code>bin/process-queue</code> to process them. Don't worry, it won't take long. Filelib
            is smart and will notice that it's just the same 22 images repeating and do next to nothing
            for the most of the queued commands.
        </p>

        <p>
            You can of course normally run multiple queue processors at the same time but do note that the JSON storage used
            in these demos by default is not atomic or robust so it will most probably crash and burn if you do so.
        </p>


        <ul>
            <?php foreach($uploaders as $up): ?>
                <li><?php echo $up; ?></li>
            <?php endforeach; ?>
        </ul>
    </body>
</html>
