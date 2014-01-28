<?php

use Pekkis\Queue\Processor\Processor;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$output = new ConsoleOutput();
$processor = new Processor($filelib->getQueue(), $output);

do {
    $ret = $processor->process();
} while ($ret);
