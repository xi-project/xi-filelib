<?php

require_once __DIR__ . '/../bootstrap.php';

// Upload a picture of a manatee
$file = $filelib->uploadFile(__DIR__ . '/../manatees/manatus-02.jpg');

// Display the image
header("Content-Type: " . $file->getMimetype());
echo file_get_contents($filelib->getStorage()->retrieve($file->getResource()));

