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
        $type = 'test';
        $data = array('message' => 'All your base are belong to us');

        $message = Message::create($type, $data);

        $this->assertEquals($data, $message->getData());
        $this->assertEquals($type, $message->getType());
        $this->assertUuid($message->getUuid());

    }

    /**
     * @test
     */
    public function shouldBeRestorableFromArray()
    {
        $arr = array(
            'uuid' => 'lussutus-uuid',
            'type' => 'lussutusviesti',
            'data' => array('lussutappa' => 'tussia')
        );

        $message = Message::fromArray($arr);

        $this->assertEquals($arr['data'], $message->getData());
        $this->assertEquals($arr['uuid'], $message->getUuid());
        $this->assertEquals($arr['type'], $message->getType());
    }

    /**
     * @test
     */
    public function internalDataShouldWork()
    {
        $message = Message::create('luss', array('mussutus' => 'kovaa mussutusta'));

        $this->assertNull($message->getIdentifier());
        $this->assertSame($message, $message->setIdentifier('loso'));
        $this->assertEquals('loso', $message->getIdentifier());

    }

}
