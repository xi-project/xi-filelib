<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\ZendSlugifier;
use Xi\Transliterator\IntlTransliterator;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Folder\Folder;

use Xi\Filelib\Authorization\Adapter\SimpleAuthorizationAdapter;
use Xi\Filelib\Authorization\AuthorizationPlugin;
use Xi\Filelib\Authorization\AccessDeniedException;

require_once __DIR__ . '/../bootstrap.php';

$writable = (isset($_GET['writable'])) ? $_GET['writable'] : false;
$publishable = (isset($_GET['publishable'])) ? $_GET['publishable'] : false;

$authorizationAdapter = new SimpleAuthorizationAdapter();
$authorizationPlugin = new AuthorizationPlugin($authorizationAdapter);
$filelib->addPlugin($authorizationPlugin, array('default'));

$authorizationAdapter
    ->setFolderWritable($writable)
    ->setFileReadableByAnonymous($publishable);

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

$folder = $filelib->getFolderOperator()->createByUrl('pictures/of/very beaütiful manatees');

try {
    $file = $filelib->upload(__DIR__ . '/../manatees/manatus-12.jpg', $folder);
    $publisher->publish($file);

} catch (AccessDeniedException $e) {

    echo  $e->getMessage();
    die();

}






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
