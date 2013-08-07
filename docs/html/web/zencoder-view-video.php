<?php

use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\File\File;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$id = $_GET['id'];

$file = $filelib->getFileOperator()->find($id);

if ($file->getStatus() == File::STATUS_COMPLETED) {
    if (!$publisher->isPublished($file)) {
        $publisher->publish($file);
    }
}


?>

<html>
<head>
    <title>Funny Joonas</title>
</head>
<body>

<pre>
    <?php
    var_dump($file);

    var_dump($file->getVersions());

    var_dump($file->getResource()->getVersions());

    ?>
</pre>

<h1>Funny Joonas video</h1>

<p>A very funny video of Joonas has been uploaded and queued for processing.</p>

<p>
    You may start processing it with <code>bin/zencoder-queue.php</code> and madly refresh this page.
    When the video is processed Joonas will appear. Ta da!
</p>

<video poster="<?php echo $publisher->getUrlVersion($file, '720p_webm_thumbnail'); ?>" controls=true>
    <source src="<?php echo $publisher->getUrlVersion($file, '720p_webm'); ?>" type='video/webm; codecs="vp8.0, vorbis"'/>
    <source src="<?php echo $publisher->getUrlVersion($file, '720p_ogv'); ?>" type='video/ogg; codecs="theora, vorbis"'/>
    <p>Oh noes, video not playable!</p>
</video>


</body>
</html>

