<?php

use Xi\Filelib\Queue\PhpAMQPQueue;

$queue = new PhpAMQPQueue(
    RABBITMQ_HOST,
    5672,
    RABBITMQ_USERNAME,
    RABBITMQ_PASSWORD,
    RABBITMQ_VHOST,
    'filelib_example',
    'filelib_example_queue'
);
$filelib->setQueue($queue);
