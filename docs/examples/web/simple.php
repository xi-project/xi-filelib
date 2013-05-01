<?php

require_once __DIR__ . '/../bootstrap.php';

use Xi\Filelib\Renderer\SimpleRenderer;

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg');


$renderer = new SimpleRenderer($filelib);
$renderer->render($file);

// @todo: PHPRenderer
// header("Content-Type: " . $file->getMimeType());
// echo $filelib->getStorage()->retrieve($file->getResource())->fpassthru();
