<?php

namespace Xi\Tests\Filelib\Queue;

use Xi\Filelib\Queue\SQSQueue;

class SQSQueueTest extends \Xi\Tests\Filelib\Queue\TestCase
{
    
    public function setUp()
    {
        if (S3_KEY === 's3_key') {
            $this->markTestSkipped('S3 not configured');
        }
        
        $this->queue = new SQSQueue(S3_KEY, S3_SECRETKEY);
     
        parent::setUp();
    }
    
}

