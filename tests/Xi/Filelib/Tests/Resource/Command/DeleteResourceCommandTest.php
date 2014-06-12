<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Resource\Command\DeleteResourceCommand;
use Xi\Filelib\Events;

class DeleteResourceCommandTest extends \Xi\Filelib\Tests\TestCase
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
                $this->equalTo(Events::RESOURCE_BEFORE_DELETE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::RESOURCE_AFTER_DELETE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $op = $this->getMockedFileRepository();

        $resource = Resource::create(array('id' => 1));

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('deleteResource')
            ->with($this->equalTo($resource));

        $storage = $this->getMockedStorageAdapter();

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
            ->method('delete')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'));

        $backend
            ->expects($this->once())
            ->method('deleteResource')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'));

        $command = new DeleteResourceCommand($resource);
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Resource\Command\DeleteResourceCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.resource.delete', $command->getTopic());
    }
}
