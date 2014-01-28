<?php

use Pekkis\Queue\QueueProcessor;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$output = new ConsoleOutput();
$processor = new QueueProcessor($filelib, $output);

do {
    $ret = $processor->process();
} while ($ret);
