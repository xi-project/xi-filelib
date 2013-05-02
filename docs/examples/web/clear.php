<?php

use Xi\Filelib\Renderer\SymfonyRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap-plugins.php';

$files = $filelib->getFileOperator()->findAll();

foreach ($files as $file) {
    $filelib->deleteFile($file);
}

var_dump($files);
