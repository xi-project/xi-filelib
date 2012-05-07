<?php

namespace Xi\Filelib\Queue;

interface Queue
{
    
    /**
     * Enqueues stuff
     * 
     * @param mixed $object
     */
    public function enqueue($object);
    
    /**
     * Dequeues stuff
     * 
     * @return mixed
     */
    public function dequeue();
    
    /**
     * Purges the queue
     */
    public function purge();
    
}

