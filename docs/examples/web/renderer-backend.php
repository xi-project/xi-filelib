<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../renderer-common.php';

use Xi\Filelib\Renderer\Renderer;
use Xi\Filelib\Renderer\Adapter\SymfonyRendererAdapter;

$id = $_GET['id'];
$version = $_GET['version'];
$download = isset($_GET['download']) ? $_GET['download'] : false;

$renderer = new Renderer(
    $filelib,
    new SymfonyRendererAdapter()
);

$symfonyResponse = $renderer->render($id, $version, array('download' => $download));

header("Content-Type: lusso/lus", true);

// $symfonyResponse->send();

die();
