<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;

use Xi\Filelib\File\Command\DeleteFileCommand;

class DeleteFileCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\DeleteFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\DeleteFileCommand'));
    }


    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getAcl'))
                    ->getMock();

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new DeleteFileCommand($op, $file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }



    /**
     * @test
     */
    public function deleteShouldDelegateCorrectly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $dispatcher->expects($this->at(0))->method('dispatch')
                   ->with($this->equalTo('file.delete'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'getProfile', 'createCommand'))
                   ->getMock();

       $unpublishCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UnpublishFileCommand')
                                ->disableOriginalConstructor()
                                ->getMock();
       $unpublishCommand->expects($this->once())->method('execute');

       $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UnpublishFileCommand'))
          ->will($this->returnValue($unpublishCommand));



        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $file = File::create(array('id' => 1, 'profile' => 'lussen', 'resource' => new Resource()));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('deleteFile')->with($this->equalTo($file));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('delete')->with($this->isInstanceOf('Xi\Filelib\File\Resource'));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));


        $command = new DeleteFileCommand($op, $file);
        $command->execute();

    }



}

