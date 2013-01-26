<?php

namespace Xi\Filelib\Tests\Queue;

use Xi\Filelib\Queue\Message;

class MessageTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function messageShouldInitializeProperly()
    {
        $body = 'All your base are belong to us';

        $message = new Message($body);

        $this->assertEquals($body, $message->getBody());
    }


    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function messageInitializationShouldThrowExceptionWhenBodyIsNotString()
    {
        $body = array('tussi');

        $message = new Message($body);
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWork()
    {
        $message = new Message('luss');
        $identifier = 'lussentuf';

        $this->assertNull($message->getIdentifier());
        $this->assertSame($message, $message->setIdentifier($identifier));

        $this->assertEquals($identifier, $message->getIdentifier());

    }



}
