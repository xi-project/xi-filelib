<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\SequentialLinker;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;

require_once __DIR__ . '/../bootstrap.php';

$publisher = new Publisher(
    $filelib,
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/files', '600', '700', 'files'),
    new SequentialLinker()
);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin, array('default'));

$file = $filelib->upload(__DIR__ . '/../manatees/manatus-09.jpg');
$publisher->publish($file);

?>

<html>
    <head>
        <title>Mighty manatee</title>
    </head>
    <body>
        <h1>You just published a picture of a manatee</h1>

        <p>
            <img src="<?php echo $publisher->getUrlVersion($file, 'original'); ?>" />
        </p>
    </body>
</html>
