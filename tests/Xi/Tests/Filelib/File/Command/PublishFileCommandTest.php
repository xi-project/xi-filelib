<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;

use Xi\Filelib\File\Command\PublishFileCommand;

class PublishFileCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\PublishFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\PublishFileCommand'));
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

        $command = new PublishFileCommand($op, $file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }



    /**
     * @test
     */
    public function publishShouldDelegateCorrectlyWhenProfileAllowsPublicationOfOriginalFile()
    {
        $file = File::create(array('id' => 1, 'profile' => 'lussen'));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'getProfile'))
                   ->getMock();

        $dispatcher->expects($this->once())->method('dispatch')
                   ->with($this->equalTo('file.publish'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));


        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getPublishOriginal')->will($this->returnValue(true));

        $profile->expects($this->never())->method('getPlugins');

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('publish')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $op->expects($this->atLeastOnce())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));

        $command = new PublishFileCommand($op, $file);
        $command->execute();

    }


    /**
     * @test
     */
    public function publishShouldDelegateCorrectlyWhenProfileDisallowsPublicationOfOriginalFile()
    {
        $file = File::create(array('id' => 1, 'profile' => 'lussen'));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $dispatcher->expects($this->once())->method('dispatch')
                   ->with($this->equalTo('file.publish'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'getProfile'))
                   ->getMock();

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getPublishOriginal')->will($this->returnValue(false));

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->never())->method('publish');

        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $op->expects($this->atLeastOnce())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));

        $command = new PublishFileCommand($op, $file);
        $command->execute();


    }

}

