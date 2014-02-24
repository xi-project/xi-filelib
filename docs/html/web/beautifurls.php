<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\ZendSlugifier;
use Xi\Transliterator\IntlTransliterator;

use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Folder\Folder;

require_once __DIR__ . '/../bootstrap.php';

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/files', '600', '700', 'files'),
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
        'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/../watermark.png', 'se', 10),
    )
);
$filelib->addPlugin($versionPlugin);

$folder = $filelib->getFolderRepository()->createByUrl('pictures/of/very beaütiful manatees');

$file = $filelib->upload(__DIR__ . '/../manatees/manatus-12.jpg', $folder);
$publisher->publish($file);

?>

<html>
    <head>
        <title>Mighty manatee</title>
    </head>
    <body>
        <h1>You just published a picture of a manatee and a cinemascope thumbnail. Aww!!!</h1>
        <p>
            <img src="<?php echo $publisher->getUrlVersion($file, 'original'); ?>" />
        </p>

        <p>
            <img src="<?php echo $publisher->getUrlVersion($file, 'cinemascope'); ?>" />
        </p>

    </body>
</html>
