<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;
use Xi\Filelib\Backend\Platform\DoctrineOrmPlatform;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Linker\SequentialLinker;
use Xi\Filelib\File\FileProfile;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\Plugin\Image\VersionPlugin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xi\Filelib\Acl\SimpleAcl;
use Xi\Filelib\Queue\PhpAMQPQueue;
use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;
use Xi\Filelib\Plugin\Image\Command\WatermarkCommand;


// 01. Basic Wiring

$path = __DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity';

$paths = array(
    __DIR__ . '/../../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity'
);

$isDevMode = true;

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'user'     => 'root',
    'password' => 'g04753m135',
    'dbname'   => 'filelib_example',
);
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

$filelib = new FileLibrary(
    new FilesystemStorage(realpath(__DIR__ . '/../data/private'), new LeveledDirectoryIdCalculator()),
    new DoctrineOrmPlatform($entityManager),
    new SymlinkFilesystemPublisher(realpath(__DIR__ . '/files'), 0600, 0700, '/files'),
    new EventDispatcher()
);
$filelib->setTempDir(__DIR__ . '/../data/temp');

// Non-mandatory


$filelib->setAcl(new SimpleAcl(true));

$filelib->setQueue(
    new PhpAMQPQueue('dr-kobros.com', 5672, 'pekkis', 'g04753m135', 'filelib', 'filelib_exchange', 'filelib_queue')
);

// 02. Profiles

$filelib->addProfile(new FileProfile('default', new SequentialLinker()));

// 03. Plugins

$filelib->addPlugin(new RandomizeNamePlugin(), array('default'));

// @todo: refactor to singular versionplugin with many versions

$filelib->addPlugin(
    new VersionPlugin(
        'thumbster',
        $filelib->getTempDir(),
        'jpg',
        array(
            'imageMagickOptions' => array(
                'imageCompression' => 8,
                'imageFormat' => 'jpg',
                'imageCompressionQuality' => 50
            ),
            'commands' => array(
                'scale' => new ExecuteMethodCommand('scaleImage', array(640, 480, true)),
                'watermark' => new WatermarkCommand(__DIR__ . '/../watermark.png', 'se', 10),
            )
        )
    ),
    array('default')
);


// 04. Basic operation
// @todo: Shortcuts (via regex? xxx(File|Folder|Resource) => (File|Folder)Operator->xxx())

$folder = $filelib->findRootFolder();
//$folder = $filelib->getFolderOperator()->findRoot();

$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg', $folder);
// $file = $filelib->getFileOperator()->upload(__DIR__ . '/../manatees/manatus-02.jpg', $folder);


$request = Request::createFromGlobals();
$renderer = new SymfonyRenderer($filelib);

// 05. Rendering



// $renderer = new SymfonyRenderer($filelib->getPuuppa(), $filelib->getLoso(), $filelib->getSitä(), $filelib->getTätä());

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

</body>

</html>


<?php

$tpl = ob_get_clean();
$response->setContent($tpl);
$response->send();

