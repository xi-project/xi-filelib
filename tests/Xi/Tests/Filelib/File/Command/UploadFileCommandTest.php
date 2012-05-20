<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\Upload\FileUpload;

class UploadFileCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UploadFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\UploadFileCommand'));
    }


    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function commandShouldFailIfAclForbidsUploadToFolder()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getAcl'))
                   ->getMock();

        $folder = Folder::create(array('id' => 1));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFolderWritable')->with($this->equalTo($folder))->will($this->returnValue(false));

        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));


        $command = new UploadFileCommand($op, $path, $folder, 'versioned');
        $command->execute();


        // $op->upload($path, $folder, 'versioned');

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

        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getAcl', 'getProfile', 'getBackend', 'getStorage', 'publish', 'getInstance'))
                   ->getMock();

        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $op->expects($this->atLeastOnce())->method('getInstance')->will($this->returnValue($fileitem));

        $fileitem->expects($this->at(0))->method('setStatus')->with($this->equalTo(File::STATUS_RAW));

        $dispatcher->expects($this->at(0))->method('dispatch')
                   ->with($this->equalTo('file.beforeUpload'), $this->isInstanceOf('Xi\Filelib\Event\FileUploadEvent'));

        $dispatcher->expects($this->at(1))->method('dispatch')
                   ->with($this->equalTo('file.upload'), $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));


        $folder = Folder::create(array('id' => 1));
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->any())->method('getLink')->will($this->returnValue('maximuslincitus'));

        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('upload')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        $backend->expects($this->once())->method('createResource')->with($this->isInstanceOf('Xi\Filelib\File\Resource'));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('store')->with($this->isInstanceOf('Xi\Filelib\File\Resource'));

        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFolderWritable')->with($this->equalTo($folder))->will($this->returnValue(true));

        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $op->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $op->expects($this->atLeastOnce())
           ->method('getProfile')
           ->with($this->equalTo('versioned'))
           ->will($this->returnValue($profile));

        $command = new UploadFileCommand($op, $path, $folder, 'versioned');
        $ret = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\File\Command\AfterUploadFileCommand', $ret);

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

         $folder = $this->getMock('Xi\Filelib\Folder\Folder');
         $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
         $profile = 'lussenhof';

         $upload = new FileUpload($path);

         $command = new UploadFileCommand($op, $upload, $folder, $profile);

         $serialized = serialize($command);

         $command2 = unserialize($serialized);

         $this->assertAttributeEquals($folder, 'folder', $command2);
         $this->assertAttributeSame($profile, 'profile', $command2);
         $this->assertAttributeEquals($upload, 'upload', $command2);

    }





}

