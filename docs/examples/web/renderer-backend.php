<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../renderer-common.php';

use Xi\Filelib\Renderer\AcceleratedRenderer;
use Xi\Filelib\Renderer\Adapter\SymfonyRendererAdapter;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\Renderer\Adapter\SimpleRendererAdapter;

// Set this to true to enable acceleration
$enableAcceleration = true;

$id = $_GET['id'];
$version = $_GET['version'];
$download = isset($_GET['download']) ? $_GET['download'] : false;

$symfonyRendererAdapter = new SymfonyRendererAdapter();
$symfonyRendererAdapter->setRequest(Request::createFromGlobals());

$renderer = new AcceleratedRenderer(
    $filelib,
    $symfonyRendererAdapter,
    realpath(__DIR__ . '/../data/private'),
    '/protected'
);
$renderer->enableAcceleration($enableAcceleration);

$symfonyResponse = $renderer->render($id, $version, array('download' => $download));
$symfonyResponse->send();
