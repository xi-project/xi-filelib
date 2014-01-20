<?php

namespace Xi\Filelib\Queue;

use Xi\Filelib\Queue\Adapter\Adapter;

class Queue
{
    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Enqueues message
     *
     * @param Enqueueable $enqueueable
     */
    public function enqueue(Enqueueable $enqueueable)
    {
        $message = $enqueueable->getMessage();

        return $this->adapter->enqueue($enqueueable);
    }

    /**
     * Dequeues message
     *
     * @return Message
     */
    public function dequeue()
    {
        return $this->adapter->dequeue();
    }

    /**
     * Purges the queue
     */
    public function purge()
    {
        return $this->adapter->purge();
    }

    /**
     * Acknowledges message
     *
     * @param Message $message
     */
    public function ack(Message $message)
    {
        return $this->adapter->ack($message);
    }
}
