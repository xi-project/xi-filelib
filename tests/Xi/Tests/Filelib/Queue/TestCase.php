<?php

namespace Xi\Tests\Filelib\Queue;

use Xi\Filelib\FileLibrary;

use Xi\Filelib\Queue\Queue;

class TestCase extends \Xi\Tests\TestCase
{
    /**
     *
     * @var Queue
     */
    protected $queue;
    
    protected $message;
    
    
    public function setUp()
    {
        $this->message = array('all your base' => 'are belong to us', 'dr' => 'vesala');
    }
    
    /**
     * @test
     * @return Queue
     */
    public function enqueueShouldEnqueueMessage()
    {
        $this->queue->enqueue($this->message);
        
        return $this->queue;
    }
    
    /**
     * @test
     * @depends enqueueShouldEnqueueMessage
     * @param type $queue 
     */
    public function dequeueShouldDequeueMessage($queue)
    {
        $message = $queue->dequeue();
        
        $this->assertEquals($this->message, $message);
    }
    
    /**
     * @test
     */
    public function dequeueShouldReturnNullIfQueueIsEmpty()
    {
        $message = $this->queue->dequeue();
        $this->assertNull($message);
    }
    
    
    
}