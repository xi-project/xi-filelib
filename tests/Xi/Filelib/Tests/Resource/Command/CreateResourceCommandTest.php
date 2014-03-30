<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Resource\Command\CreateResourceCommand;
use Xi\Filelib\Events;

class CreateResourceCommandTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function commandExecutes()
    {
        $ed = $this->getMockedEventDispatcher();

        $ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::RESOURCE_BEFORE_CREATE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::RESOURCE_AFTER_CREATE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $op = $this->getMockedFileRepository();

        $resource = Resource::create(array('id' => 1));

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('createResource')
            ->with($this->equalTo($resource));

        $storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'rere' => $op,
                'storage' => $storage,
                'ed' => $ed,
                'backend' => $backend
            )
        );

        $storage
            ->expects($this->once())
            ->method('store')
            ->with(
                $this->isInstanceOf('Xi\Filelib\Resource\Resource'),
                $this->isType('string')
            );

        $backend
            ->expects($this->once())
            ->method('createResource')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'));

        $command = new CreateResourceCommand($resource, 'lussenhofer');
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $resource = Resource::create(array('id' => 1));
        $command = new CreateResourceCommand($resource, 'lussenhofer');
        $serialized = serialize($command);
        $command2 = unserialize($serialized);
        $this->assertAttributeEquals($resource, 'resource', $command2);
        $this->assertAttributeEquals('lussenhofer', 'path', $command2);
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Resource\Command\CreateResourceCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.resource.create', $command->getTopic());
    }
}
