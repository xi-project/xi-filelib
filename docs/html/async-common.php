<?php

use Pekkis\Queue\Adapter\PhpAMQPAdapter;

$adapter = new PhpAMQPAdapter(
    RABBITMQ_HOST,
    5672,
    RABBITMQ_USERNAME,
    RABBITMQ_PASSWORD,
    RABBITMQ_VHOST,
    'filelib_example',
    'filelib_example_queue'
);

// Filelib creates its queue with our adapter
$filelib->createQueueFromAdapter($adapter);

$queue = $filelib->getQueue();
