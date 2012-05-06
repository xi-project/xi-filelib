<?php

namespace Xi\Tests\Filelib\Queue;

use Xi\Filelib\Queue\RabbitMQQueue;

class RabbitMQQueueTest extends \Xi\Tests\Filelib\Queue\TestCase
{
    
    public function setUp()
    {
        if (!RABBITMQ_HOST) {
            $this->markTestSkipped('RabbitMQ not configured');
        }
        
        $this->queue = new RabbitMQQueue(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USERNAME, RABBITMQ_PASSWORD, RABBITMQ_VHOST, 'filelib_test_exchange', 'filelib_test_queue');
        
        parent::setUp();
    }
    
}

