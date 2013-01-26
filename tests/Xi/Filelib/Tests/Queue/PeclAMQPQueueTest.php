<?php

namespace Xi\Filelib\Tests\Queue;

use Xi\Filelib\Queue\PeclAMQPQueue;

class PeclAMQPQueueTest extends \Xi\Filelib\Tests\Queue\TestCase
{

    protected function getQueue()
    {
        return new PeclAMQPQueue(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USERNAME, RABBITMQ_PASSWORD, RABBITMQ_VHOST, 'filelib_test_exchange', 'filelib_test_queue');
    }


    public function setUp()
    {
        // $this->markTestSkipped("Pecl AMQP has serious issues. Skip until it's better.");

        if (!class_exists("\AMQPConnection")) {
            $this->markTestSkipped("AMQP PECL extension required");
        }

        if (!RABBITMQ_HOST) {
            $this->markTestSkipped('RabbitMQ not configured');
        }

        parent::setUp();
    }

}

