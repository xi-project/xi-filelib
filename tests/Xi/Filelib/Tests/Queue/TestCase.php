<?php

namespace Xi\Filelib\Tests\Queue;

use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Tests\Queue\TestCommand;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
{
    /**
     *
     * @var Queue
     */
    protected $queue;

    protected $message;

    public function setUp()
    {
        $this->message = new TestCommand();
        $this->queue = $this->getQueue();
        $this->queue->purge();
    }

    abstract protected function getQueue();

    /**
     * @test
     * @return Queue
     */
    public function dequeueShouldDequeueEnqueuedMessage()
    {
        $this->queue->enqueue($this->message);

        $message = $this->queue->dequeue();
        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $message);
        $this->queue->ack($message);

        $this->assertEquals($this->message, unserialize($message->getBody()));
        $this->assertNotNull($message->getIdentifier());

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
     */
    public function purgeShouldResultInAnEmptyQueue()
    {
        for ($x = 10; $x <= 10; $x++) {
            $this->queue->enqueue(new TestCommand());
        }

        $msg = $this->queue->dequeue();
        $this->assertNotNull($msg);
        $this->queue->ack($msg);

        $this->queue->purge();

        $this->assertNull($this->queue->dequeue());

    }

   /**
     * @test
     */
    public function queueShouldResendIfMessageIsNotAcked()
    {
        $queue = $this->getQueue();
        $queue->purge();

        $this->assertNull($queue->dequeue());

        $message = new TestCommand();
        $queue->enqueue($message);

        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $queue->dequeue());
        $this->assertNull($queue->dequeue());

        unset($queue);
        gc_collect_cycles();

        $queue = $this->getQueue();

        $msg = $queue->dequeue();
        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $msg);

        $queue->ack($msg);

    }

}
