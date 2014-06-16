<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../renderer-common.php';

use Xi\Filelib\Renderer\AcceleratedRenderer;
use Xi\Filelib\Renderer\Adapter\SymfonyRendererAdapter;
use Symfony\Component\HttpFoundation\Request;
use Xi\Filelib\Renderer\Events;
use Xi\Filelib\Event\RenderEvent;
use Symfony\Component\HttpFoundation\Response;

// Hook into the render loop and replace error with a more funny version
$filelib->getEventDispatcher()->addListener(
    Events::RENDERER_RENDER,
    function (RenderEvent $event) {
        /** @var Response $response */
        $response = $event->getAdaptedResponse();
        if ($response->getStatusCode() == 404) {
            $response->setContent('Oh noes, version not found!!!');
        }
    }
);

// Set this to true to enable acceleration
$enableAcceleration = false;

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
