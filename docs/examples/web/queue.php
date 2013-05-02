<?php

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\EnqueueableCommand;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap-plugins.php';
require_once __DIR__ . '/../bootstrap-queue.php';

$iter = new DirectoryIterator(__DIR__ . '/../manatees');

$filelib->getFileOperator()->setCommandStrategy(FileOperator::COMMAND_UPLOAD, EnqueueableCommand::STRATEGY_ASYNCHRONOUS);

$folder = $filelib->getFolderOperator()->findRoot();

for ($x = 1; $x <= 10; $x++) {
    foreach ($iter as $file) {
        if (preg_match("/\.jpg$/", $file->getPathName())) {
            echo "Asynchronously uploading: " . $file->getPathName() . "<br />";
            $filelib->getFileOperator()->upload($file->getPathName(), $folder);
        }
    }
}
