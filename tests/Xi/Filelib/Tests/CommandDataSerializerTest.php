<?php

namespace Xi\Filelib\Tests;

use Pekkis\Queue\Message;
use Xi\Filelib\CommandDataSerializer;

class CommandDataSerializerTest extends \Xi\Filelib\Tests\TestCase
{
    private $filelib;

    /**
     * @var CommandDataSerializer
     */
    private $serializer;

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\CommandDataSerializer');
        $this->assertImplements('Pekkis\Queue\Data\DataSerializer', 'Xi\Filelib\CommandDataSerializer');
    }

    public function setUp()
    {
        $this->filelib = $this->getMockedFilelib();
        $this->serializer = new CommandDataSerializer($this->filelib);
    }

    /**
     * @test
     */
    public function willNotSerializeRandomStuff()
    {
        $unserialized = array();
        $this->assertFalse($this->serializer->willSerialize($unserialized));
    }

    /**
     * @test
     */
    public function willSerializeCommand()
    {
        $command = new NullCommand();
        $this->assertTrue($this->serializer->willSerialize($command));
    }

    /**
     * @test
     */
    public function serializes()
    {
        $command = new NullCommand();
        $serialized = $this->serializer->serialize($command);
        return $serialized;
    }

    /**
     * @test
     * @depends serializes
     */
    public function unserializes($serialized)
    {
        $command = $this->serializer->unserialize($serialized);
        $this->assertInstanceOf('Xi\Filelib\Tests\NullCommand', $command);
    }
}
