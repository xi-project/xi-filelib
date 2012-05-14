<?php

namespace Xi\Filelib\Queue;

interface Queue
{

    /**
     * Enqueues message
     *
     * @param Message $message
     */
    public function enqueue(Message $message);

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

