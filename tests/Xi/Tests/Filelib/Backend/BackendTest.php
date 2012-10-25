<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Tests\Filelib\TestCase;
use ArrayIterator;

class BackendTest extends TestCase
{

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $im;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $platform;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    public function setUp()
    {
        $this->platform = $this->getMock('Xi\Filelib\Backend\Platform\Platform');
        $this->im = $this->getMock('Xi\Filelib\IdentityMap\IdentityMap');
        $this->ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->backend = new Backend($this->ed, $this->platform, $this->im);
    }

    /**
     * @test
     */
    public function getNumberOfReferencesShouldDelegateToPlatform()
    {
        $resource = Resource::create(array('id' => 1));
        $this->platform->expects($this->once())->method('getNumberOfReferences')->with($resource)
                       ->will($this->returnValue(55));
        $ret = $this->backend->getNumberOfReferences($resource);
        $this->assertEquals(55, $ret);
    }


    /**
     * @test
     */
    public function getEventDispatcherShouldReturnEventDispatcher()
    {
        $this->assertSame($this->ed, $this->backend->getEventDispatcher());
    }

    /**
     * @test
     */
    public function getIdentityMapShouldReturnIdentityMap()
    {
        $this->assertSame($this->ed, $this->backend->getEventDispatcher());
    }

    /**
     * @test
     */
    public function getPlatformShouldReturnPlatform()
    {
        $this->assertSame($this->platform, $this->backend->getPlatform());
    }

    /**
     * @test
     */
    public function getUuidGeneratorShouldReturnCachedPhpUuidGenerator()
    {
        $ug = $this->backend->getUuidGenerator();
        $this->assertInstanceOf('Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator', $this->backend->getUuidGenerator());
    }

    /**
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $this->assertRegexp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $this->backend->generateUuid());
    }

    /**
     * @test
     */
    public function updateResourceShouldDelegateToPlatform()
    {
        $obj = Resource::create(array('id' => 1));
        $this->platform->expects($this->once())->method('assertValidIdentifier')->with($obj);
        $this->platform->expects($this->once())->method('updateResource')->with($obj)->will($this->returnValue(true));
        $ret = $this->backend->updateResource($obj);
        $this->assertTrue($ret);

    }

    /**
     * @test
     */
    public function updateFolderShouldDelegateToPlatform()
    {
        $obj = Folder::create(array('id' => 1));
        $this->platform->expects($this->once())->method('assertValidIdentifier')->with($obj);
        $this->platform->expects($this->once())->method('updateFolder')->with($obj)->will($this->returnValue(true));
        $ret = $this->backend->updateFolder($obj);
        $this->assertTrue($ret);
    }


    /**
     * @test
     */
    public function updateFileShouldThrowExceptionWhenFolderIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Exception\FolderNotFoundException');

        $resource = Resource::create(array('id' => 2));
        $file = File::create(array('id' => 1, 'resource' => $resource, 'folder_id' => 666));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findFolder', 'updateResource'))
            ->getMock();

        $this->platform->expects($this->once())->method('assertValidIdentifier')->with($file);

        $backend->expects($this->once())->method('findFolder')->with($file->getFolderId())
            ->will($this->returnValue(false));

        $backend->expects($this->never())->method('updateResource');
        $this->platform->expects($this->never())->method('updateFile');

        $ret = $backend->updateFile($file);
        $this->assertTrue($ret);
    }


    /**
     * @test
     */
    public function updateFileShouldDelegateToPlatform()
    {
        $resource = Resource::create(array('id' => 2));
        $folder = Folder::create(array('id' => 666));
        $file = File::create(array('id' => 1, 'resource' => $resource, 'folder_id' => 666));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
                        ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
                        ->setMethods(array('findFolder', 'updateResource'))
                        ->getMock();

        $this->platform->expects($this->once())->method('assertValidIdentifier')->with($file);

        $backend->expects($this->once())->method('findFolder')->with($file->getFolderId())
                ->will($this->returnValue($folder));

        $backend->expects($this->once())->method('updateResource')->with($resource);

        $this->platform->expects($this->once())->method('updateFile')->with($file)->will($this->returnValue(true));
        $ret = $backend->updateFile($file);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function findResourceShouldTryIdentityMapAndExitEarlyWhenFound()
    {
        $obj = Resource::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\File\Resource')
             ->will($this->returnValue($obj));
        $this->platform->expects($this->never())->method('findResourcesByIds');
        $this->im->expects($this->never())->method('addMany');

        $ret = $this->backend->findResource(1);
        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
    }

    /**
     * @test
     */
    public function findResourceShouldTryIdentityMapAndDelegateToPlatformWhenNotFound()
    {
        $obj = Resource::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\File\Resource')
            ->will($this->returnValue(false));
        $this->platform->expects($this->once())->method('findResourcesByIds')
              ->with(array(1))->will($this->returnValue(new ArrayIterator(array($obj))));
        $this->im->expects($this->once())->method('addMany')->with($this->isInstanceOf('ArrayIterator'));

        $ret = $this->backend->findResource(1);
        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);

    }

    /**
     * @test
     */
    public function findFileShouldTryIdentityMapAndExitEarlyWhenFound()
    {
        $obj = File::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\File\File')
            ->will($this->returnValue($obj));
        $this->platform->expects($this->never())->method('findFilesByIds');
        $this->im->expects($this->never())->method('addMany');

        $ret = $this->backend->findFile(1);
        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);
    }

    /**
     * @test
     */
    public function findFileShouldTryIdentityMapAndDelegateToPlatformWhenNotFound()
    {
        $obj = File::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\File\File')
            ->will($this->returnValue(false));
        $this->platform->expects($this->once())->method('findFilesByIds')
            ->with(array(1))->will($this->returnValue(new ArrayIterator(array($obj))));
        $this->im->expects($this->once())->method('addMany')->with($this->isInstanceOf('ArrayIterator'));

        $ret = $this->backend->findFile(1);
        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);

    }

    /**
     * @test
     */
    public function findFolderShouldTryIdentityMapAndExitEarlyWhenFound()
    {
        $obj = Folder::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($obj));
        $this->platform->expects($this->never())->method('findFoldersByIds');
        $this->im->expects($this->never())->method('addMany');

        $ret = $this->backend->findFolder(1);
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $ret);
    }

    /**
     * @test
     */
    public function findFolderShouldTryIdentityMapAndDelegateToPlatformWhenNotFound()
    {
        $obj = Folder::create(array('id' => 1));
        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));
        $this->platform->expects($this->once())->method('findFoldersByIds')
            ->with(array(1))->will($this->returnValue(new ArrayIterator(array($obj))));
        $this->im->expects($this->once())->method('addMany')->with($this->isInstanceOf('ArrayIterator'));

        $ret = $this->backend->findFolder(1);
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $ret);

    }

    /**
     * @test
     */
    public function createResourceShouldDelegateToPlatformAndAddToIdentityMap()
    {
        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->once())->method('createResource')
            ->with($obj)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('add')->with($obj)->will($this->returnValue(true));

        $ret = $this->backend->createResource($obj);
        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);

    }

    /**
     * @test
     */
    public function createFolderShouldDelegateToPlatformAndAddToIdentityMap()
    {
        $parent = Folder::create(array('id' => 66, 'parent_id' => null));
        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->once())->method('createFolder')
            ->with($obj)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('add')->with($obj)->will($this->returnValue(true));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findFolder'))
            ->getMock();

        $backend->expects($this->once())->method('findFolder')->with(66)->will($this->returnValue($parent));

        $ret = $backend->createFolder($obj);

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $ret);

    }

    /**
     * @test
     */
    public function createFolderShouldThrowExceptionWhenParentFolderIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Exception\FolderNotFoundException');

        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->never())->method('createFolder');
        $this->im->expects($this->never())->method('add');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findFolder'))
            ->getMock();

        $backend->expects($this->once())->method('findFolder')->with(66)->will($this->returnValue(false));

        $ret = $backend->createFolder($obj);

    }


    /**
     * @test
     */
    public function createFileShouldDelegateToPlatformAndAddToIdentityMap()
    {
        $folder = Folder::create(array('id' => 1));
        $file = File::create(array('id' => 1));

        $this->platform->expects($this->once())->method('createFile')
            ->with($file, $folder)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('add')->with($file)->will($this->returnValue(true));

        $ret = $this->backend->createFile($file, $folder);
        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);

    }


    /**
     * @test
     */
    public function deleteFolderShouldDelegateToPlatformAndRemoveFromIdentityMap()
    {
        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->once())->method('deleteFolder')
            ->with($obj)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('remove')->with($obj)->will($this->returnValue(true));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findFilesIn'))
            ->getMock();

        $backend->expects($this->once())->method('findFilesIn')->with($obj)
                ->will($this->returnValue(new ArrayIterator(array())));

        $ret = $backend->deleteFolder($obj);

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $ret);

    }

    /**
     * @test
     */
    public function deleteFolderShouldThrowExceptionWhenFolderContainsFiles()
    {
        $this->setExpectedException('Xi\Filelib\Exception\FolderNotEmptyException');

        $files = array(
            File::create(array('id' => 1)),
            File::create(array('id' => 2)),
        );

        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->never())->method('deleteFolder');

        $this->im->expects($this->never())->method('remove');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findFilesIn'))
            ->getMock();

        $backend->expects($this->once())->method('findFilesIn')->with($obj)
            ->will($this->returnValue(new ArrayIterator($files)));

        $ret = $backend->deleteFolder($obj);

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $ret);

    }


    /**
     * @test
     */
    public function deleteFileShouldDelegateToPlatformAndRemoveFromIdentityMap()
    {
        $obj = File::create(array('id' => 1));

        $this->platform->expects($this->once())->method('deleteFile')
            ->with($obj)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('remove')->with($obj)->will($this->returnValue(true));

        $ret = $this->backend->deleteFile($obj);

        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);

    }


    /**
     * @test
     */
    public function deleteResourceShouldThrowExceptionWhenItHasReferences()
    {
        $this->setExpectedException('Xi\Filelib\Exception\ResourceReferencedException');

        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->never())->method('deleteResource');
        $this->ed->expects($this->never())->method('dispatch');
        $this->im->expects($this->never())->method('remove');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('getNumberOfReferences'))
            ->getMock();

        $backend->expects($this->once())->method('getNumberOfReferences')->with($obj)
            ->will($this->returnValue(6));

        $backend->deleteResource($obj);

    }


    /**
     * @test
     */
    public function deleteResourceShouldDelegateToPlatformAndRemoveFromIdentityMap()
    {
        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->once())->method('deleteResource')
            ->with($obj)->will($this->returnArgument(0));

        $this->im->expects($this->once())->method('remove')->with($obj)->will($this->returnValue(true));

        $this->ed->expects($this->once())->method('dispatch')
             ->with('resource.delete', $this->isInstanceOf('Xi\Filelib\Event\ResourceEvent'));

        $ret = $this->backend->deleteResource($obj);


        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);

    }



}
