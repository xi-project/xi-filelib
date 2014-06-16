<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lazy-publisher-common.php';

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-25.jpg');
$publisher->publish($file);
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

            <p>
                <img src="<?php echo $publisher->getUrl($file, 'cinemascope'); ?>" />
            </p>

        </div>
    </body>
</html>
