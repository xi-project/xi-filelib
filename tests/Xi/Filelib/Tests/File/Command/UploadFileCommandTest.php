<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use ArrayIterator;
use Xi\Filelib\Events;

class UploadFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UploadFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\UploadFileCommand'));
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
        $filelib = $this->getMockedFilelib();
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile', 'getBackend', 'getStorage', 'generateUuid', 'createCommand', 'executeOrQueue'))
                   ->getMock();

        $fileitem = $this->getMock('Xi\Filelib\File\File');

        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_WRITE_TO, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FileUploadEvent'));

        $dispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $folder = Folder::create(array('id' => 1));
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $profile = $this->getMockedFileProfile();

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
                        ->setConstructorArgs(array($path, $folder, 'versioned'))
                        ->setMethods(array('getResource'))
                        ->getMock();
        $command->attachTo($this->getMockedFilelib(null, $op));

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
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $profile = 'lussenhof';

        $upload = new FileUpload($path);

        $command = new UploadFileCommand($upload, $folder, $profile);

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

        $filelib = $this->getMockedFilelib();

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getBackend', 'getProfile'))
                    ->getMock();

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $profile = $this->getMockedFileProfile();

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

        $command = new UploadFileCommand($upload, $folder, $profile);
        $command->attachTo($this->getMockedFilelib(null, $op));
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

        $filelib = $this->getMockedFilelib();

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('getBackend', 'getProfile'))
            ->getMock();

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $profile = $this->getMockedFileProfile();

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

        $command = new UploadFileCommand($upload, $folder, $profile);
        $command->attachTo($this->getMockedFilelib(null, $op));

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
        $filelib = $this->getMockedFilelib();

        $op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('getBackend', 'getProfile'))
                    ->getMock();

        $profile = $this->getMockedFileProfile();

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

        $command = new UploadFileCommand($upload, $folder, $profile);
        $command->attachTo($this->getMockedFilelib(null, $op));

        $ret = $command->getResource($file, $upload);

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
        $this->assertSame('first-id', $ret->getId());
    }

}
