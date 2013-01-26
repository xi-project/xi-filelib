<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\UnPublishFileCommand;

class UnPublishFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UnPublishFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\UnPublishFileCommand'));
    }


    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getAcl'))
                    ->getMock();

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

        $command = new UnPublishFileCommand($op, $file);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }



    /**
     * @test
     */
    public function unpublishShouldDelegateCorrectly()
    {
        $file = File::create(array('id' => 1, 'profile' => 'lussen'));

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $dispatcher->expects($this->once())->method('dispatch')
                   ->with($this->equalTo('xi_filelib.file.unpublish'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('publish', 'getProfile', 'getPublisher'))
                   ->getMock();

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('unpublish');

        $op->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));

        $command = new UnpublishFileCommand($op, $file);
        $command->execute();

    }



}

