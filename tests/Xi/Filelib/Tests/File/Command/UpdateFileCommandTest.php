<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\UpdateFileCommand;

class UpdateFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UpdateFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\UpdateFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $op = $this->getMockedFileOperator();
        $op->expects($this->any())->method('generateUuid')->will($this->returnValue('xooxer'));

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new UpdateFileCommand($op, $file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }

    /**
     * @test
     */
    public function updateShouldDelegateCorrectly()
    {
        $filelib = $this->getMockedFilelib();
        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));
        $ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
            $this->equalTo('xi_filelib.file.update'),
            $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
        );

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile', 'createCommand'))
                   ->getMock();

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $profile = $this->getMockedFileProfile();

        $file = $this->getMockedFile();
        $file->setProfile('lussenhofer');

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussenhofer'))->will($this->returnValue($profile));

        $command = new UpdateFileCommand($op, $file);
        $command->execute();

    }
}
