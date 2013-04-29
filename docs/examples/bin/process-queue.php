<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap-plugins.php';
require_once __DIR__ . '/../bootstrap-queue.php';

use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;

$processor = new DefaultQueueProcessor($filelib);

while ($ret = $processor->process()) {
    var_dump($ret);
}

echo "no moar processing to do...";


