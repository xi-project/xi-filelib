<?php

namespace Xi\Filelib\Tests\Queue;

use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Queue\Message;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
{
    /**
     *
     * @var Queue
     */
    protected $queue;

    /**
     * @var Message
     */
    protected $message;

    public function setUp()
    {
        $this->message = Message::create('test-message', array('aybabtu' => 'lussentus'));
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

        $this->assertEquals($this->message->getData(), $message->getData());
        $this->assertEquals($this->message->getUuid(), $message->getUuid());
        $this->assertEquals($this->message->getType(), $message->getType());

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
            $this->queue->enqueue(Message::create('testosteron', array('count' => $x)));
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

        $message = Message::create('testosteron', array('mucho' => 'masculino'));
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
