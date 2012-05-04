<?php

namespace Xi\Filelib\Queue;

interface Queue
{
    
    public function enqueue($object);
    
    public function dequeue();
    
}

