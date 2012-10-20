<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\File\Command\PublishFileCommand;

class AfterUploadFileCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AfterUploadFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\AfterUploadFileCommand'));
    }



    public function provideDataForUploadTest()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }


    /**
     * @test
     * @dataProvider provideDataForUploadTest
     */
    public function commandShouldUploadAndDelegateCorrectly($expectedCallToPublish, $readableByAnonymous)
    {


        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));


        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getAcl', 'getProfile', 'getBackend', 'getStorage', 'publish', 'getInstance', 'createCommand'))
                   ->getMock();



        if ($expectedCallToPublish) {


            $publishCommand = $this->getMockBuilder('Xi\Filelib\File\Command\PublishFileCommand')
                                   ->disableOriginalConstructor()
                                   ->getMock();
            $publishCommand->expects($this->once())->method('execute');



            $op->expects($this->once())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\PublishFileCommand'))
               ->will($this->returnValue($publishCommand));
        }



        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Platform\Platform');
        $backend->expects($this->once())->method('updateFile')->with($this->isInstanceOf('Xi\Filelib\File\File'));

        $fileitem->expects($this->any())->method('getProfile')->will($this->returnValue('versioned'));

        $fileitem->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $fileitem->expects($this->once())->method('setStatus')->with($this->equalTo(File::STATUS_COMPLETED));

        $dispatcher->expects($this->at(0))->method('dispatch')
                   ->with($this->equalTo('file.afterUpload'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->any())->method('getLink')->will($this->returnValue('maximuslincitus'));

        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFileReadableByAnonymous')->with($this->isInstanceOf('Xi\Filelib\File\File'))->will($this->returnValue($readableByAnonymous));

        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getAcl'))
                    ->getMock();

        $file = File::create(array('id' => 1, 'profile' => 'versioned'));

                $command = new AfterUploadFileCommand($op, $file);



        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);
    }





}

