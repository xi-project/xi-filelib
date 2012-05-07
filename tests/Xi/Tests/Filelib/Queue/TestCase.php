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
        return $queue;
    }
    
    /**
     * @test
     */
    public function dequeueShouldReturnNullIfQueueIsEmpty()
    {
        $message = $this->queue->dequeue();
        $this->assertNull($message);
    }
    
    
    /**
     * @test
     * @depends dequeueShouldDequeueMessage
     */
    public function purgeShouldResultInAnEmptyQueue($queue)
    {
        for ($x = 10; $x <= 10; $x++) {
            $queue->enqueue("Pitikö olla hoppu, nyt se on leikin loppu");
        }
        
        $this->assertNotNull($queue->dequeue());
        
        $queue->purge();
        
        $this->assertNull($queue->dequeue());
        
        
    }
    
    
    
}