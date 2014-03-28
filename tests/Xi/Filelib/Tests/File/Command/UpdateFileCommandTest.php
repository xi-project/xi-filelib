<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\UpdateFileCommand;
use Xi\Filelib\Events;
use Xi\Filelib\Resource\Resource;

class UpdateFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UpdateFileCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\File\Command\UpdateFileCommand'));
    }

    /**
     * @test
     */
    public function updateShouldDelegateCorrectly()
    {
        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::FILE_BEFORE_UPDATE),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
            $this->equalTo(Events::FILE_AFTER_UPDATE),
            $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
        );

        $op = $this->getMockedFileRepository();

        $file = $this->getMockedFile();

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib = $this->getMockedFilelib(
            null,
            $op,
            null,
            null,
            $ed,
            $backend
        );

        $command = new UpdateFileCommand($file);
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new UpdateFileCommand($file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileRepository', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\UpdateFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
        $this->assertEquals('xi_filelib.command.file.update', $command->getTopic());
    }

}
