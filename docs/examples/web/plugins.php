<?php

use Xi\Filelib\Renderer\SymfonyRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap-plugins.php';

$folder = $filelib->findRootFolder();
// $folder = $filelib->getFolderOperator()->findRoot();

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg', $folder);
// $file = $filelib->getFileOperator()->upload(__DIR__ . '/../manatees/manatus-02.jpg', $folder);

$request = Request::createFromGlobals();

// $renderer = new SymfonyRenderer($filelib->getPuuppa(), $filelib->getLoso(), $filelib->getSitä(), $filelib->getTätä());
$renderer = new SymfonyRenderer($filelib);

/*
$renderer->setRequest($request);

$response = $renderer->render($file, array('version' => 'thumbster'));

$response->send();

die();
*/

// 06. Advanced rendering

$response = new Response();


ob_start();
?>

    <html>
    <head>
        <title>Hello Filebanksta</title>
    </head>

    <body>

    <h1>Original</h1>

    <img src="<?php echo $renderer->getUrl($file, array('version' => 'original')); ?>" />

    <h2>Thumbster</h2>

    <img src="<?php echo $renderer->getUrl($file, array('version' => 'thumbster')); ?>" />

    <h2>Ribuls</h2>

    <img src="<?php echo $renderer->getUrl($file, array('version' => 'ribuls')); ?>" />

    </body>

    </html>


<?php

$tpl = ob_get_clean();
$response->setContent($tpl);
$response->send();



// 04. Basic operation
// Shortcuts (via regex? xxx(File|Folder|Resource) => (File|Folder)Operator->xxx())
