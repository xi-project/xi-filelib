<?php

use Pekkis\Queue\Adapter\PhpAMQPAdapter;
use Pekkis\Queue\Queue;

$adapter = new PhpAMQPAdapter(
    RABBITMQ_HOST,
    5672,
    RABBITMQ_USERNAME,
    RABBITMQ_PASSWORD,
    RABBITMQ_VHOST,
    'filelib_example',
    'filelib_example_queue'
);

$queue = new Queue($adapter);
$filelib->setQueue($queue);
