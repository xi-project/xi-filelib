<?php

require_once __DIR__ . '/../bootstrap.php';

// $folder = $filelib->findRootFolder();
$folder = $filelib->getFolderOperator()->findRoot();

// $file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg', $folder);
$file = $filelib->getFileOperator()->upload(__DIR__ . '/../manatees/manatus-02.jpg', $folder);

var_dump($file);
