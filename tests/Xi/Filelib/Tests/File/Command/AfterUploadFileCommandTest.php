<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\Events;

class AfterUploadFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AfterUploadFileCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\File\Command\AfterUploadFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldUploadAndDelegateCorrectly()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $op = $this->getMockedFileOperator(array('versioned'));

        $file = $this->getMockedFile('versioned');

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $filelib = $this->getMockedFilelib(
            null,
            $op,
            null,
            null,
            $dispatcher,
            $backend
        );

        $backend->expects($this->once())
            ->method('updateFile')
            ->with($file);

        $file
            ->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(File::STATUS_COMPLETED));

        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::FILE_AFTER_AFTERUPLOAD),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $command = new AfterUploadFileCommand($file);
        $command->attachTo($filelib);
        $ret = $command->execute();

        $this->assertSame($file, $ret);
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new AfterUploadFileCommand($file);
        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.file.after_upload', $command->getTopic());
    }
}
