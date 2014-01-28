<?php

use Pekkis\Queue\Processor\Processor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Xi\Filelib\Queue\FilelibMessageHandler;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';

$output = new ConsoleOutput();
$processor = new Processor($filelib->getQueue(), $output);

$messageHandler = new FilelibMessageHandler();
$messageHandler->attachTo($filelib);

$processor->registerHandler(
    $messageHandler
);

try {

    do {
        $ret = $processor->process();

    } while ($ret);

} catch (\Exception $e) {
    $output->writeln(sprintf("CRITICAL: %s", $e->getMessage()));
}

