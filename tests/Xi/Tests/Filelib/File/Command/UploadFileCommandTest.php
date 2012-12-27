<?php

namespace Xi\Tests\Filelib\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use ArrayIterator;

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
        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
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
                   ->setMethods(array('getAcl', 'getProfile', 'getBackend', 'getStorage', 'publish', 'getInstance', 'generateUuid', 'createCommand', 'executeOrQueue'))
                   ->getMock();

        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $op->expects($this->once())->method('generateUuid')
           ->will($this->returnValue('uusi-uuid'));

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

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $backend
            ->expects($this->once())
            ->method('createFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'));

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

        $afterUploadCommand = $this->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $op->expects($this->once())
           ->method('createCommand')
           ->with($this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'))
           ->will($this->returnValue($afterUploadCommand));

        $op->expects($this->once())
           ->method('executeOrQueue')
           ->with($this->isInstanceOf('Xi\Filelib\File\Command\AfterUploadFileCommand'));

        $op->expects($this->once())->method('executeOrQueue')
           ->with($this->isInstanceOf('Xi\Filelib\File\Command\AfterUploadFileCommand'));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                        ->setConstructorArgs(array($op, $path, $folder, 'versioned'))
                        ->setMethods(array('getResource'))
                        ->getMock();

        $command->expects($this->once())->method('getResource')
                ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->isInstanceOf('Xi\Filelib\File\Upload\FileUpload'))
                ->will($this->returnValue(Resource::create()));

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
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $profile = 'lussenhof';

        $upload = new FileUpload($path);

        $command = new UploadFileCommand($op, $upload, $folder, $profile);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeSame($profile, 'profile', $command2);
        $this->assertAttributeEquals($upload, 'upload', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }

    /**
     * @test
     */
    public function getResourceShouldGenerateNewResourceIfProfileAllowsButNoResourceIsFound()
    {
        $file = File::create(array());

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getBackend', 'getProfile'))
                    ->getMock();

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $op->expects($this->any())->method('getProfile')
            ->with($this->equalTo('lussenhof'))
            ->will($this->returnValue($profile));

        $profile->expects($this->once())
            ->method('isSharedResourceAllowed')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(true));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());


        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
                ->with($this->equalTo($finder))
                ->will($this->returnValue(new ArrayIterator(array())));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new UploadFileCommand($op, $upload, $folder, $profile);
        $ret = $command->getResource($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
        $this->assertSame($hash, $ret->getHash());
    }


    /**
     * @test
     */
    public function getResourceShouldGenerateNewResourceIfProfileRequires()
    {
        $file = File::create(array());

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('getBackend', 'getProfile'))
            ->getMock();

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $op->expects($this->any())->method('getProfile')
            ->with($this->equalTo('lussenhof'))
            ->will($this->returnValue($profile));

        $profile->expects($this->atLeastOnce())
            ->method('isSharedResourceAllowed')
            ->will($this->returnValue(false));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());

        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(new ArrayIterator(array(
                Resource::create(array('id' => 'first-id')),
                Resource::create(array('id' => 'second-id')),
            ))));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new UploadFileCommand($op, $upload, $folder, $profile);

        $ret = $command->getResource($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
        $this->assertSame($hash, $ret->getHash());
    }



    /**
     * @test
     */
    public function getResourceShouldReuseResourceIfProfileAllowsAndResourcesAreFound()
    {
        $file = File::create(array());
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getBackend', 'getProfile'))
                    ->getMock();

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $op->expects($this->any())->method('getProfile')
           ->with($this->equalTo('lussenhof'))
           ->will($this->returnValue($profile));

        $profile->expects($this->once())
                ->method('isSharedResourceAllowed')
                ->will($this->returnValue(true));

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $profile = 'lussenhof';
        $upload = new FileUpload($path);
        $hash = sha1_file($upload->getRealPath());

        $finder = new ResourceFinder(
            array(
                'hash' => $hash,
            )
        );
        $backend->expects($this->once())->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(new ArrayIterator(array(
            Resource::create(array('id' => 'first-id')),
            Resource::create(array('id' => 'second-id')),
        ))));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new UploadFileCommand($op, $upload, $folder, $profile);

        $ret = $command->getResource($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
        $this->assertSame('first-id', $ret->getId());
    }



}

