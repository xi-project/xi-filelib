<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\CopyFileCommand;
use Xi\Filelib\File\Upload\FileUpload;

class CopyFileCommandTest extends \Xi\Tests\Filelib\TestCase
{

    protected $op;
    protected $fop;
    protected $folder;
    protected $ack;

    public function setUp()
    {
        $this->op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->disableOriginalConstructor()
                    ->setMethods(array('getFolderOperator', 'getAcl', 'findByFilename', 'getBackend', 'getEventDispatcher', 'getStorage', 'createCommand', 'generateUuid'))
                    ->getMock();

        $this->fop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
                    ->disableOriginalConstructor()
                    ->setMethods(array())
                    ->getMock();

        $this->acl = $this->getMock('Xi\Filelib\Acl\Acl');

        $this->op->expects($this->any())->method('getFolderOperator')->will($this->returnValue($this->fop));

        $this->op->expects($this->any())->method('getAcl')->will($this->returnValue($this->acl));

        $this->folder = $this->getMock('Xi\Filelib\Folder\Folder');
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\CopyFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\CopyFileCommand'));
    }


    public function provideNames()
    {
        return array(
            array('tohtori-vesala copy.jpg', 'tohtori-vesala.jpg'),
            array('tohtori-vesala copy 2.jpg', 'tohtori-vesala copy.jpg'),
            array('tohtori-vesala copy 3.jpg', 'tohtori-vesala copy 2.jpg'),
            array('tussinlussutus losoposki tussu copy 666', 'tussinlussutus losoposki tussu copy 665'),
            array('lisko-mikko copy 563', 'lisko-mikko copy 562'),
            array('## copy', '##'),
        );
    }

    /**
     * @test
     * @dataProvider provideNames
     */
    public function getCopyNameShouldGenerateCorrectCopyName($expected, $originalName)
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg'));

        $command = new CopyFileCommand($this->op, $file, $this->folder);

        $ret = $command->getCopyName($originalName);

        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function getCopyNameShouldThrowExceptionWhenItCannotResolveCopyName()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg'));

        $command = new CopyFileCommand($this->op, $file, $this->folder);

        $ret = $command->getCopyName('');

    }

    /**
     * @test
     */
    public function getImpostorShouldReturnEqualFileIfOriginalFileIsNotFoundInFolder()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'versions' => array('tussi', 'lussi')));

        $this->op->expects($this->once())->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala.jpg'))
             ->will($this->returnValue(false));

        $this->op->expects($this->once())->method('generateUuid')
                 ->will($this->returnValue('uusi-uuid'));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\CopyFileCommand')
                        ->setMethods(array('handleImpostorResource'))
                        ->setConstructorArgs(array($this->op, $file, $this->folder))
                        ->getMock();
        $command->expects($this->once())->method('handleImpostorResource');

        $impostor = $command->getImpostor($file);

        $this->assertCount(0, $impostor->getVersions());

        $this->assertEquals($file->getName(), $impostor->getName());
        $this->assertEquals('uusi-uuid', $impostor->getUuid());
    }


    /**
     * @test
     */
    public function getImpostorShouldIterateUntilFileIsNotFoundInFolder()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'versions' => array('tussi', 'lussi')));

        $this->folder->expects($this->any())->method('getId')->will($this->returnValue(666));

        $this->op->expects($this->at(1))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala.jpg'))
             ->will($this->returnValue(true));

        $this->op->expects($this->at(2))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala copy.jpg'))
             ->will($this->returnValue(true));

        $this->op->expects($this->at(3))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala copy 2.jpg'))
             ->will($this->returnValue(false));

        $this->op->expects($this->once())->method('generateUuid')
                 ->will($this->returnValue('uusi-uuid'));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\CopyFileCommand')
                        ->setMethods(array('handleImpostorResource'))
                        ->setConstructorArgs(array($this->op, $file, $this->folder))
                        ->getMock();
        $command->expects($this->once())->method('handleImpostorResource');

        $impostor = $command->getImpostor();
        $this->assertCount(0, $impostor->getVersions());

        $this->assertEquals('uusi-uuid', $impostor->getUuid());

        $this->assertEquals('tohtori-vesala copy 2.jpg', $impostor->getName());
        $this->assertEquals(666, $impostor->getFolderId());

    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function commandShouldthrowExceptionIfAclForbidsFolderWrite()
    {
        $this->acl->expects($this->once())->method('isFolderWritable')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnValue(false));

        $file = File::create(array('name' => 'tohtori-vesala.jpg'));

        $command = new CopyFileCommand($this->op, $file, $this->folder);
        $command->execute();

    }

    /**
     * @return array
     */
    public function provideDataForCommandExecution()
    {
        return array(
            array(true),
            array(false),
        );
    }


    /**
     * @test
     * @dataProvider provideDataForCommandExecution
     */
    public function commandShouldExecuteWhenAclAllowsFolderWrite($exclusiveResource)
    {
        $this->acl->expects($this->once())->method('isFolderWritable')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnValue(true));

        $backend = $this->getMock('Xi\Filelib\Backend\Backend');
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->op->expects($this->once())->method('generateUuid')
                 ->will($this->returnValue('uusi-uuid'));

        $this->op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $this->op->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        $this->op->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'resource' => Resource::create(array('exclusive' => $exclusiveResource))));

        $backend->expects($this->once())->method('upload')->with($this->isInstanceOf('Xi\Filelib\File\File'));

        if ($exclusiveResource) {

            $storage->expects($this->once())->method('retrieve')
                     ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                     ->will($this->returnValue('xooxoo'));
            $storage->expects($this->once())->method('store')
                    ->with($this->isInstanceOf('Xi\Filelib\File\Resource'), $this->equalTo('xooxoo'));

            $backend->expects($this->once())->method('createResource')
                    ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                    ->will($this->returnArgument(0));
        } else {
            $storage->expects($this->never())->method('retrieve');
            $storage->expects($this->never())->method('store');
            $backend->expects($this->never())->method('createResource');
        }

        $eventDispatcher->expects($this->once())->method('dispatch')
                        ->with($this->equalTo('file.copy'), $this->isInstanceOf('Xi\Filelib\Event\FileCopyEvent'));

        $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->op->expects($this->any())->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'))
                 ->will($this->returnValue($afterUploadCommand));

        $afterUploadCommand->expects($this->once())->method('execute')->will($this->returnValue($file));

        $command = new CopyFileCommand($this->op, $file, $this->folder);
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

         $folder = $this->getMock('Xi\Filelib\Folder\Folder');
         $file = $this->getMock('Xi\Filelib\File\File');

         $command = new CopyFileCommand($op, $file, $folder);

         $serialized = serialize($command);
         $command2 = unserialize($serialized);

         $this->assertAttributeEquals($file, 'file', $command2);
         $this->assertAttributeEquals($folder, 'folder', $command2);
         $this->assertAttributeNotEmpty('uuid', $command2);
    }

}

