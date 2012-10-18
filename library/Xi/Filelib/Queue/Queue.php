<?php

namespace Xi\Filelib\Queue;

interface Queue
{

    /**
     * Enqueues message
     *
     * @param Enqueueable $enqueueable
     */
    public function enqueue(Enqueueable $enqueueable);

    /**
     * Dequeues message
     *
     * @return Message
     */
    public function dequeue();

    /**
     * Purges the queue
     */
    public function purge();

    /**
     * Acknowledges message
     *
     * @param Message $message
     */
    public function ack(Message $message);


}

