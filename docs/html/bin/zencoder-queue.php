<?php

use Pekkis\Queue\Processor\Processor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Xi\Filelib\Queue\FilelibMessageHandler;
use Pekkis\Queue\Processor\ConsoleOutputSubscriber as ProcessorConsoleOutputSubscriber;
use Pekkis\Queue\ConsoleOutputSubscriber;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';
require_once __DIR__ . '/../zencoder-common.php';

$output = new ConsoleOutput();
$queueSubscriber = new ConsoleOutputSubscriber($output);
$processorSubscriber = new ProcessorConsoleOutputSubscriber($output);

$queue->addSubscriber($queueSubscriber);
$queue->addSubscriber($processorSubscriber);

$processor = new Processor($queue);

$messageHandler = new FilelibMessageHandler();
$messageHandler->attachTo($filelib);

$processor->registerHandler($messageHandler);

try {

    do {
        $ret = $processor->process();

    } while ($ret);

} catch (\Exception $e) {
    $output->writeln(sprintf("CRITICAL: %s", $e->getMessage()));
}


