<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileRepository;
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

    public function provideSerialization()
    {
        return array(
            array(false),
            array(true)
        );
    }

    /**
     * @test
     * @dataProvider provideSerialization
     */
    public function commandShouldUploadAndDelegateCorrectly($serialize)
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $file = $this->getMockedFile('versioned');
        $file
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('xooxer'));


        $command = $this->getMockedCommand();
        $command
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($file));

        $op = $this->getMockedFileRepository();
        $op
            ->expects($this->once())
            ->method('createCommand')
            ->with(
                'Xi\Filelib\File\Command\UpdateFileCommand',
                array($file)
            )
            ->will($this->returnValue($command));

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

        if ($serialize) {
            $command = unserialize(serialize($command));

            $op
                ->expects($this->once())
                ->method('find')
                ->with('xooxer')
                ->will($this->returnValue($file));
        }

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

        $this->assertAttributeEquals(null, 'fileRepository', $command2);
        $this->assertAttributeEquals(1, 'file', $command2);
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
