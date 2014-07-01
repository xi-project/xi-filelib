<?php

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Adapter\AmazonS3PublisherAdapter;
use Xi\Filelib\Publisher\Linker\ReversibleCreationTimeLinker;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';

$publisher = new Publisher(
    new SymlinkFilesystemPublisherAdapter(__DIR__ . '/files', '600', '700', 'files'),
    // new AmazonS3PublisherAdapter(S3_KEY, S3_SECRETKEY, S3_BUCKET),
    new ReversibleCreationTimeLinker()
);
$publisher->attachTo($filelib);

$originalPlugin = new OriginalVersionPlugin('original');
$filelib->addPlugin($originalPlugin, array('default'));

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-09.jpg');
$publisher->publishAllVersions($file);

?>

<html>
    <head>
        <title>Filelib Examples</title>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
        <link href="filelib.css" rel="stylesheet">
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    </head>
    <body>

        <div class="container">

            <h1>You just published a picture of a manatee</h1>

            <p>
                <img src="<?php echo $publisher->getUrl($file, 'original'); ?>" />
            </p>

        </div>
    </body>
</html>
