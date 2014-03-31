<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Resource\Command\UpdateResourceCommand;
use Xi\Filelib\Events;

class UpdateResourceCommandTest extends \Xi\Filelib\Tests\TestCase
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
                $this->equalTo(Events::RESOURCE_BEFORE_UPDATE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::RESOURCE_AFTER_UPDATE),
                $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent')
            );

        $op = $this->getMockedFileRepository();

        $resource = Resource::create(array('id' => 1));

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('updateResource')
            ->with($this->equalTo($resource));


        $filelib = $this->getMockedFilelib(
            null,
            array(
                'rere' => $op,
                'ed' => $ed,
                'backend' => $backend
            )
        );

        $backend
            ->expects($this->once())
            ->method('updateResource')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'));

        $command = new UpdateResourceCommand($resource);
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Resource\Command\UpdateResourceCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.resource.update', $command->getTopic());
    }
}
