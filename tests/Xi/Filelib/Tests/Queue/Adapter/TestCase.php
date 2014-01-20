<?php

namespace Xi\Filelib\Tests\Queue\Adapter;

use Xi\Filelib\Queue\Adapter\Adapter;
use Xi\Filelib\Queue\Message;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
{
    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Message
     */
    protected $message;

    public function setUp()
    {
        $this->message = Message::create('test-message', array('aybabtu' => 'lussentus'));
        $this->adapter = $this->getAdapter();
        $this->adapter->purge();
    }

    abstract protected function getAdapter();

    /**
     * @test
     * @return Queue
     */
    public function dequeueShouldDequeueEnqueuedMessage()
    {
        $this->adapter->enqueue($this->message);

        $message = $this->adapter->dequeue();
        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $message);
        $this->adapter->ack($message);

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
        $message = $this->adapter->dequeue();
        $this->assertNull($message);
    }

    /**
     * @test
     */
    public function purgeShouldResultInAnEmptyQueue()
    {
        for ($x = 10; $x <= 10; $x++) {
            $this->adapter->enqueue(Message::create('testosteron', array('count' => $x)));
        }

        $msg = $this->adapter->dequeue();
        $this->assertNotNull($msg);
        $this->adapter->ack($msg);

        $this->adapter->purge();

        $this->assertNull($this->adapter->dequeue());

    }

   /**
     * @test
     */
    public function queueShouldResendIfMessageIsNotAcked()
    {
        $queue = $this->getAdapter();
        $queue->purge();

        $this->assertNull($queue->dequeue());

        $message = Message::create('testosteron', array('mucho' => 'masculino'));
        $queue->enqueue($message);

        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $queue->dequeue());
        $this->assertNull($queue->dequeue());

        unset($queue);
        gc_collect_cycles();

        $queue = $this->getAdapter();

        $msg = $queue->dequeue();
        $this->assertInstanceOf('Xi\Filelib\Queue\Message', $msg);

        $queue->ack($msg);

    }

}
