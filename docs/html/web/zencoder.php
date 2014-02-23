<?php

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

if (is_file(__DIR__ . '/../filelib-example.json')) {
    unlink(__DIR__ . '/../filelib-example.json');
}


require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$path = realpath(__DIR__ . '/../../../tests/data/hauska-joonas.mp4');

$filelib->getFileOperator()->setExecutionStrategy(
    FileOperator::COMMAND_AFTERUPLOAD,
    ExecutionStrategy::STRATEGY_ASYNCHRONOUS
);

$file = $filelib->getFileOperator()->upload($path);

header('Location: zencoder-view-video.php?id=' . $file->getId());

