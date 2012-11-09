<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Command\UpdateFileCommand;

class UpdateFileCommandTest extends \Xi\Tests\Filelib\TestCase
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getAcl'))
                    ->getMock();

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
    public function updateShouldDelegateCorrectlyWhenFileCanNotBePublished()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'getProfile', 'getAcl', 'createCommand'))
                   ->getMock();

       $unpublishCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UnpublishFileCommand')
                                ->disableOriginalConstructor()
                                ->getMock();
       $unpublishCommand->expects($this->once())->method('execute');

       $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UnpublishFileCommand'))
          ->will($this->returnValue($unpublishCommand));

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));


        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');

        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));


        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMockBuilder('Xi\Filelib\File\File')
                     ->setMethods(array('setLink'))
                     ->getMock();

        $file->setProfile('lussenhofer');

        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));


        // $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        // $op->expects($this->never())->method('publish');

        $acl->expects($this->atLeastOnce())->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(false));


        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussenhofer'))->will($this->returnValue($profile));


        $command = new UpdateFileCommand($op, $file);
        $command->execute();

    }

    /**
     * @test
     */
    public function updateShouldDelegateCorrectlyWhenFileCanBePublished()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'getProfile', 'getAcl', 'createCommand'))
                   ->getMock();

       $unpublishCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UnpublishFileCommand')
                                ->disableOriginalConstructor()
                                ->getMock();
       $unpublishCommand->expects($this->once())->method('execute');

       $publishCommand = $this->getMockBuilder('Xi\Filelib\File\Command\PublishFileCommand')
                                ->disableOriginalConstructor()
                                ->getMock();
       $publishCommand->expects($this->once())->method('execute');

       $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UnpublishFileCommand'))
          ->will($this->returnValue($unpublishCommand));

       $op->expects($this->at(3))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\PublishFileCommand'))
          ->will($this->returnValue($publishCommand));

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');

        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));

        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMockBuilder('Xi\Filelib\File\File')
                     ->setMethods(array('setLink'))
                     ->getMock();

        $file->setProfile('lussenhofer');

        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));


        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        // $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        // $op->expects($this->once())->method('publish')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussenhofer'))->will($this->returnValue($profile));

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));


        $acl->expects($this->atLeastOnce())->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(true));


        $command = new UpdateFileCommand($op, $file);
        $command->execute();

    }

}
