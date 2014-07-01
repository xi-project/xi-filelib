<?php

use Xi\Filelib\File\File;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$id = $_GET['id'];

$file = $filelib->getFileRepository()->find($id);

if ($file->getStatus() == File::STATUS_COMPLETED) {
    if (!$publisher->isPublished($file)) {
        $publisher->publishAllVersions($file);
    }
}


?>

<html>
<head>
    <title>Funny Joonas</title>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link href="filelib.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">

    <h1>Funny Joonas in HD</h1>

    <p>A very funny video of Joonas has been uploaded and queued for processing.</p>

    <p>
        You may start processing it with <code>bin/zencoder-queue.php</code> and madly refresh this page.
        When the video is processed Joonas will appear. Ta da!
    </p>

    <p>
        Captions: -"Say something funny." -"Seppo."
    </p>

    <?php if ($file->getStatus() === File::STATUS_COMPLETED): ?>

    <video poster="<?php echo $publisher->getUrl($file, '720p_webm_thumbnail'); ?>" controls=true>
        <source src="<?php echo $publisher->getUrl($file, '720p_webm'); ?>" type='video/webm; codecs="vp8.0, vorbis"'/>
        <source src="<?php echo $publisher->getUrl($file, '720p_ogv'); ?>" type='video/ogg; codecs="theora, vorbis"'/>
        <p>Oh noes, video not playable!</p>
    </video>

    <?php else: ?>

        <p><strong>Oh noes, the video is not ready yet!!</strong></p>

    <?php endif; ?>

</div>
</body>
</html>

