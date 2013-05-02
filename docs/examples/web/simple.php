<?php

require_once __DIR__ . '/../bootstrap.php';

use Xi\Filelib\Renderer\SimpleRenderer;

try {

    $file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg');

    $renderer = new SimpleRenderer($filelib);
    $renderer->render($file);
} catch (\Exception $e) {

    echo "<pre>";


}

