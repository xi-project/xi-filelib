<?php

namespace Xi\Tests\Filelib\Queue;

use Xi\Filelib\Queue\PeclAMQPQueue;

class PeclAMQPQueueTest extends \Xi\Tests\Filelib\Queue\TestCase
{
    
    public function setUp()
    {
        if (!class_exists("\AMQPConnection")) {
            $this->markTestSkipped("AMQP PECL extension required");
        }
        
        if (!RABBITMQ_HOST) {
            $this->markTestSkipped('RabbitMQ not configured');
        }
        
        $this->queue = new PeclAMQPQueue(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USERNAME, RABBITMQ_PASSWORD, RABBITMQ_VHOST, 'filelib_test_exchange', 'filelib_test_queue');
                                
        parent::setUp();
    }
    
}

