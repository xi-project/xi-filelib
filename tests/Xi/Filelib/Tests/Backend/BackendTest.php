<?php

namespace Xi\Filelib\Tests\Backend;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Collections\Collection\ArrayCollection;

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
        $this->platform = $this->getMock('Xi\Filelib\Backend\Adapter\BackendAdapter');

        $this->im = $this
            ->getMockBuilder('Xi\Filelib\Backend\IdentityMap\IdentityMap')
            ->disableOriginalConstructor()
            ->getMock();

        $this->ed = $this
            ->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->backend = new Backend($this->ed, $this->platform);
    }

    /**
     * @test
     */
    public function getNumberOfReferencesShouldDelegateToBackendAdapter()
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
    public function getBackendAdapterShouldReturnBackendAdapter()
    {
        $this->assertSame($this->platform, $this->backend->getBackendAdapter());
    }

    /**
     * @test
     */
    public function updateResourceShouldDelegateToBackendAdapter()
    {
        $obj = Resource::create(array('id' => 1));

        $this->platform
            ->expects($this->once())
            ->method('updateResource')
            ->with($obj)
            ->will($this->returnValue(true));

        $ret = $this->backend->updateResource($obj);
        $this->assertNull($ret);
    }

    /**
     * @test
     */
    public function updateFolderShouldDelegateToBackendAdapter()
    {
        $obj = Folder::create(array('id' => 1));
        $this->platform->expects($this->once())->method('updateFolder')->with($obj)->will($this->returnValue(true));
        $ret = $this->backend->updateFolder($obj);
        $this->assertNull($ret);
    }

    /**
     * @test
     */
    public function updateFileShouldThrowExceptionWhenFolderIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Backend\FolderNotFoundException');

        $resource = Resource::create(array('id' => 2));
        $file = File::create(array('id' => 1, 'resource' => $resource, 'folder_id' => 666));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findById'))
            ->getMock();

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with($file->getFolderId(), 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $backend->expects($this->never())->method('updateResource');
        $this->platform->expects($this->never())->method('updateFile');

        $backend->updateFile($file);
    }

    /**
     * @test
     */
    public function updateFileShouldDelegateToBackendAdapter()
    {
        $resource = Resource::create(array('id' => 2));
        $folder = Folder::create(array('id' => 666));
        $file = File::create(array('id' => 1, 'resource' => $resource, 'folder_id' => 666));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findById', 'updateResource'))
            ->getMock();

        $backend->expects($this->once())->method('findById')->with($file->getFolderId(), 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($folder));

        $backend->expects($this->once())->method('updateResource')->with($resource);

        $this->platform->expects($this->once())->method('updateFile')->with($file)->will($this->returnValue(true));
        $ret = $backend->updateFile($file);
        $this->assertNull($ret);
    }

    /**
     * @test
     */
    public function createResourceShouldDelegateToBackendAdapter()
    {
        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->once())->method('createResource')
            ->with($obj)->will($this->returnArgument(0));

        $backend = $this->getMockedBackend();

        $ret = $backend->createResource($obj);
        $this->assertSame($obj, $ret);
    }

    /**
     * @test
     */
    public function createFolderShouldDelegateToBackendAdapter()
    {
        $parent = Folder::create(array('id' => 66, 'parent_id' => null));
        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->once())->method('createFolder')
            ->with($obj)->will($this->returnArgument(0));

        $backend = $this->getMockedBackend(array('findById'));

        $backend
            ->expects($this->once())
            ->method('findById')
            ->with(66, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($parent));

        $ret = $backend->createFolder($obj);

        $this->assertSame($obj, $ret);
    }

    /**
     * @test
     */
    public function createFolderShouldThrowExceptionWhenParentFolderIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Backend\FolderNotFoundException');

        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->never())->method('createFolder');
        $this->im->expects($this->never())->method('add');

        $backend = $this->getMockedBackend(array('findById'));
        $backend
            ->expects($this->once())
            ->method('findById')
            ->with(66, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $backend->createFolder($obj);
    }

    /**
     * @test
     */
    public function createFileShouldThrowExceptionWithNonUniqueFile()
    {
        $backend = $this->getMockedBackend(array('findByFinder', 'getIdentityMapHelper'));

        $folder = Folder::create(array('id' => 1, 'name' => 'lussen'));
        $file = File::create(array('id' => 1, 'name' => 'ankanlipaisija'));

        $this->platform->expects($this->never())->method('createFile');

        $finder = new FileFinder(array('folder_id' => $folder->getId(), 'name' => $file->getName()));

        $nonUniqueFile = File::create(array('id' => 2, 'name' => 'ankanlipaisija'));
        $backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array($nonUniqueFile))));

        $this->setExpectedException(
            'Xi\Filelib\Backend\NonUniqueFileException',
            'A file with the name "ankanlipaisija" already exists in folder "lussen"'
        );

        $ret = $backend->createFile($file, $folder);
        $this->assertNull($ret);
    }

    /**
     * @test
     */
    public function createFileShouldDelegateToBackendAdapter()
    {
        $backend = $this->getMockedBackend(array('findByFinder', 'getIdentityMapHelper'));

        $folder = Folder::create(array('id' => 1));
        $file = File::create(array('id' => 1));

        $this->platform->expects($this->once())->method('createFile')
            ->with($file, $folder)->will($this->returnArgument(0));

        $finder = new FileFinder(array('folder_id' => $folder->getId(), 'name' => $file->getName()));

        $backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->equalTo($finder))
            ->will($this->returnValue(ArrayCollection::create(array())));

        $ret = $backend->createFile($file, $folder);
        $this->assertSame($file, $ret);
    }

    /**
     * @test
     */
    public function deleteFolderShouldDelegateToBackendAdapterAndRemoveFromIdentityMap()
    {
        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->once())->method('deleteFolder')
            ->with($obj)->will($this->returnArgument(0));

        $backend = $this->getMockedBackend(array('findByFinder'));

        $self = $this;
        $backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->isInstanceOf('Xi\Filelib\Backend\Finder\FileFinder'))
            ->will(
                $this->returnCallback(
                    function (FileFinder $finder) use ($self) {
                        $expectedParams = array(
                            'folder_id' => 1,
                        );
                        $self->assertEquals($expectedParams, $finder->getParameters());

                        return ArrayCollection::create(array());
                    }
                )
            );

        $ret = $backend->deleteFolder($obj);
        $this->assertSame($obj, $ret);
    }

    /**
     * @test
     */
    public function deleteFolderShouldThrowExceptionWhenFolderContainsFiles()
    {
        $this->setExpectedException('Xi\Filelib\Backend\FolderNotEmptyException');

        $files = array(
            File::create(array('id' => 1)),
            File::create(array('id' => 2)),
        );

        $obj = Folder::create(array('id' => 1, 'parent_id' => 66));

        $this->platform->expects($this->never())->method('deleteFolder');

        $this->im->expects($this->never())->method('remove');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('findByFinder'))
            ->getMock();

        $self = $this;
        $backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with($this->isInstanceOf('Xi\Filelib\Backend\Finder\FileFinder'))
            ->will(
                $this->returnCallback(
                    function (FileFinder $finder) use ($self, $files) {
                        $expectedParams = array(
                            'folder_id' => 1,
                        );
                        $self->assertEquals($expectedParams, $finder->getParameters());

                        return ArrayCollection::create($files);
                    }
                )
            );

        $ret = $backend->deleteFolder($obj);
        $this->assertNull($ret);
    }

    /**
     * @test
     */
    public function deleteFileShouldDelegateToBackendAdapter()
    {
        $obj = File::create(array('id' => 1));

        $this->platform->expects($this->once())->method('deleteFile')
            ->with($obj)->will($this->returnArgument(0));

        $backend = $this->getMockedBackend();

        $ret = $backend->deleteFile($obj);
        $this->assertSame($obj, $ret);
    }

    /**
     * @test
     */
    public function deleteResourceShouldThrowExceptionWhenItHasReferences()
    {
        $this->setExpectedException('Xi\Filelib\Backend\ResourceReferencedException');

        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->never())->method('deleteResource');

        $backend = $this->getMockedBackend(array('getNumberOfReferences'));

        $backend->expects($this->once())->method('getNumberOfReferences')->with($obj)
            ->will($this->returnValue(6));

        $backend->deleteResource($obj);
    }

    /**
     * @test
     */
    public function deleteResourceShouldDelegateToBackendAdapter()
    {
        $obj = Resource::create(array('id' => 1));

        $this->platform->expects($this->once())->method('deleteResource')
            ->with($obj)->will($this->returnArgument(0));

        $backend = $this->getMockedBackend();
        $ret = $backend->deleteResource($obj);
        $this->assertNull($ret);
    }

    /**
     * @return array
     */
    public function provideFinders()
    {
        return array(
            array(new FileFinder()),
            array(new FolderFinder()),
            array(new ResourceFinder())
        );
    }

    /**
     * @test
     * @dataProvider provideFinders
     * @param Finder $finder
     */
    public function findByFinderShouldTryManyFromIdentityMapAndDelegateToBackendAdapter(Finder $finder)
    {
        $this->platform->expects($this->once())->method('findByFinder')
            ->with($finder)
            ->will($this->returnValue(array(1, 2, 3, 4, 5)));

        $this->platform->expects($this->once())->method('findByIds')
            ->with($this->isInstanceOf('Xi\Filelib\Backend\FindByIdsRequest'))
            ->will($this->returnArgument(0));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('getIdentityMapHelper'))
            ->getMock();

        $ret = $backend->findByFinder($finder);
        $this->assertInstanceOf('Traversable', $ret);
    }

    /**
     * @return array
     */
    public function provideClassNames()
    {
        return array(
            array('Xi\Filelib\File\File'),
            array('Xi\Filelib\Resource\Resource'),
            array('Xi\Filelib\Folder\Folder'),
        );
    }

    /**
     * @test
     * @dataProvider provideClassNames
     * @param string $className
     */
    public function findByIdShouldDelegateToBackendAdapter($className)
    {
        $this->platform->expects($this->once())->method('findByIds')
            ->with($this->isInstanceOf('Xi\Filelib\Backend\FindByIdsRequest'))
            ->will($this->returnArgument(0));

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setConstructorArgs(array($this->ed, $this->platform, $this->im))
            ->setMethods(array('getIdentityMapHelper'))
            ->getMock();

        $ret = $backend->findById(1, $className);
        $this->assertFalse($ret);
    }


    public function getMockedBackend($methods = array())
    {
        $methods = array_unique(
            array_merge(
                array('getIdentityMapHelper'),
                $methods
            )
        );

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->ed, $this->platform))
            ->getMock();

        return $backend;
    }

    /**
     * @test
     */
    public function cacheCanBeSet()
    {
        $cache = new Cache($this->getMockedCacheAdapter());
        $this->assertNull($this->backend->getCache());
        $this->assertSame($this->backend, $this->backend->setCache($cache));
        $this->assertSame($cache, $this->backend->getCache());
    }
}
