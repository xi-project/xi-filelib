<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lazy-publisher-common.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xi\Filelib\Renderer\Adapter\SymfonyRendererAdapter;
use Xi\Filelib\Renderer\Renderer;

$request = Request::createFromGlobals();

list ($file, $version) = $publisher->reverseUrl($request->getPathInfo());

$rendererAdapter = new SymfonyRendererAdapter();
$rendererAdapter->setRequest(Request::createFromGlobals());

$renderer = new Renderer(
    $filelib,
    $rendererAdapter
);

/** @var Response $response */
$response = $renderer->render($file, $version, array('download' => false));

$expires = new \DateTime();
$expires->modify('+30 days');

$response->setExpires($expires);
$response->setPublic();

$response->send();
