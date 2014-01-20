<?php

namespace Xi\Filelib\Tests\Queue\Adapter;

use Xi\Filelib\Queue\Adapter\PhpAMQPAdapter;

class PhpAMQPAdapterTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('PhpAmqpLib\Connection\AMQPConnection')) {
            $this->markTestSkipped('PhpAmqpLib not found');
        }

        if (!RABBITMQ_HOST) {
            $this->markTestSkipped('RabbitMQ not configured');
        }

        parent::setUp();
    }

    protected function getAdapter()
    {
        return new PhpAMQPAdapter(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USERNAME, RABBITMQ_PASSWORD, RABBITMQ_VHOST, 'filelib_test_exchange', 'filelib_test_queue');
    }

}
