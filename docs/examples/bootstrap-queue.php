<?php

use Xi\Filelib\Queue\PhpAMQPQueue;

$filelib->setQueue(
    new PhpAMQPQueue('dr-kobros.com', 5672, 'pekkis', 'g04753m135', 'filelib', 'filelib_exchange', 'filelib_queue')
);
