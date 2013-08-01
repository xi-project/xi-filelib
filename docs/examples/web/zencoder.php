<?php

use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\EnqueueableCommand;


require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$path = realpath(__DIR__ . '/../../../tests/data/hauska-joonas.mp4');


$filelib->getFileOperator()->setCommandStrategy(
    FileOperator::COMMAND_AFTERUPLOAD,
    EnqueueableCommand::STRATEGY_ASYNCHRONOUS
);
$file = $filelib->getFileOperator()->upload($path);

header('Location: zencoder-view-video.php?id=' . $file->getId());

