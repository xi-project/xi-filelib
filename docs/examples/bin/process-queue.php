<?php

use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../async-common.php';

$processor = new DefaultQueueProcessor($filelib);

while ($ret = $processor->process()) {
    var_dump($ret);
}

echo "no moar processing to do...";


