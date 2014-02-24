<?php

namespace Xi\Filelib\Tests\Command;

use Pekkis\Queue\Message;
use Xi\Filelib\Command\CommandDataSerializer;

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
        $this->assertClassExists('Xi\Filelib\Command\CommandDataSerializer');
        $this->assertImplements('Pekkis\Queue\Data\DataSerializer', 'Xi\Filelib\Command\CommandDataSerializer');
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
        $this->assertInstanceOf('Xi\Filelib\Tests\Command\NullCommand', $command);
    }
}
