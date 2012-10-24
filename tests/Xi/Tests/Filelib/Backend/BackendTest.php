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


}
