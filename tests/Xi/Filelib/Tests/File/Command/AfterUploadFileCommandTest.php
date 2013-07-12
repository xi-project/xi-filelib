<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\File\Command\PublishFileCommand;

class AfterUploadFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AfterUploadFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\AfterUploadFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldUploadAndDelegateCorrectly()
    {
        $filelib = $this->getMockedFilelib();
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile', 'getBackend', 'getStorage', 'publish', 'getInstance', 'createCommand'))
                   ->getMock();

        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();
        $backend->expects($this->once())->method('updateFile')->with($this->isInstanceOf('Xi\Filelib\File\File'));

        $fileitem->expects($this->any())->method('getProfile')->will($this->returnValue('versioned'));

        $fileitem->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $fileitem->expects($this->once())->method('setStatus')->with($this->equalTo(File::STATUS_COMPLETED));

        $dispatcher->expects($this->at(0))->method('dispatch')
                   ->with($this->equalTo('xi_filelib.file.after_upload'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $profile = $this->getMockedFileProfile();

        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->any())->method('getLink')->will($this->returnValue('maximuslincitus'));

        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $op->expects($this->atLeastOnce())
           ->method('getProfile')
           ->with($this->equalTo('versioned'))
           ->will($this->returnValue($profile));

        $command = new AfterUploadFileCommand($op, $fileitem);
        $ret = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);

    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $op = $this->getMockedFileOperator();
        $op->expects($this->any())->method('generateUuid')->will($this->returnValue('xooxer'));

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new AfterUploadFileCommand($op, $file);
        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);
    }

}
