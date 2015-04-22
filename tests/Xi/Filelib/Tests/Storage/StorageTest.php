<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Storage\Events;
use Xi\Filelib\Storage\FileIOException;
use DateTime;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\Storage;

class StorageTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    private $resource;

    private $version;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    public function setUp()
    {
        $this->adapter = $this->getMockedStorageAdapter();

        $this->cache = $this
            ->getMockBuilder('Xi\Filelib\Storage\RetrievedCache')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage = new Storage(
            $this->adapter,
            $this->cache
        );
        $this->ed = $this->getMockedEventDispatcher();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'storage' => $this->storage,
                'ed' => $this->ed
            )
        );
        $this->storage->attachTo($filelib);

        $this->exception = new \Exception('Throw you like an exception');

        $this->resource = Resource::create();
        $this->version = Version::get('version');
        $this->file = File::create(array('created_at' => new DateTime()));
    }

    /**
     * @test
     */
    public function cacheDefaults()
    {
        $storage = new Storage(
            $this->getMockedStorageAdapter()
        );

        $this->assertAttributeInstanceOf(
            'Xi\Filelib\Storage\RetrievedCache',
            'cache',
            $storage
        );
    }

    /**
     * @test
     */
    public function getAdapterReturnsAdapter()
    {
        $this->assertInstanceOf('Xi\Filelib\Tool\LazyReferenceResolver', $this->storage->getAdapter());
        $this->assertSame($this->adapter, $this->storage->getAdapter()->resolve());
    }

    /**
     * @test
     */
    public function storeShouldThrowCorrectException()
    {
        $this->ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::BEFORE_STORE, $this->isInstanceOf('Xi\Filelib\Event\StorageEvent'));

        $this->adapter->expects($this->once())->method('store')
                      ->will($this->throwException($this->exception));

        try {
            $this->storage->store($this->resource, '/lus/hof');

            $this->fail("Did not throw an exception");

        } catch (FileIoException $e) {
            $this->assertSame($this->exception, $e->getPrevious());
        }

    }

    /**
     * @test
     */
    public function storeVersionShouldThrowCorrectException()
    {
        $this->ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::BEFORE_STORE, $this->isInstanceOf('Xi\Filelib\Event\StorageEvent'));


        $this->adapter->expects($this->once())->method('storeVersion')
            ->will($this->throwException($this->exception));

        try {
            $this->storage->storeVersion($this->resource, $this->version, '/lus/hof');

            $this->fail("Did not throw an exception");

        } catch (FileIOException $e) {
            $this->assertSame($this->exception, $e->getPrevious());
        }

    }

    /**
     * @test
     */
    public function retrieveShouldThrowExceptionIfFileIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');

        $this->ed->expects($this->never())->method('dispatch');

        $this->adapter
            ->expects($this->once())
            ->method('exists')
            ->with($this->resource)
            ->will($this->returnValue(false));

        $this->storage->retrieve($this->resource);
    }

    /**
     * @test
     */
    public function retrieveVersionsShouldThrowExceptionIfFileIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');

        $this->ed->expects($this->never())->method('dispatch');

        $this->adapter
            ->expects($this->once())
            ->method('versionExists')
            ->with($this->resource)
            ->will($this->returnValue(false));

        $this->storage->retrieveVersion($this->resource, Version::get('version'));
    }

    /**
     * @test
     */
    public function deleteShouldThrowExceptionIfFileIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');

        $this->adapter
            ->expects($this->once())
            ->method('exists')
            ->with($this->resource)
            ->will($this->returnValue(false));

        $this->storage->delete($this->resource);
    }

    /**
     * @test
     */
    public function deleteVersionsShouldThrowExceptionIfFileIsNotFound()
    {
        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');

        $this->adapter
            ->expects($this->once())
            ->method('versionExists')
            ->with($this->resource)
            ->will($this->returnValue(false));

        $this->storage->deleteVersion($this->resource, Version::get('version'));
    }

    /**
     * @test
     */
    public function storeDelegates()
    {
        $resource = Resource::create();
        $path = '/tenhunen/lipaisee.lus';

        $this->adapter
            ->expects($this->once())
            ->method('store')
            ->with($resource, $path)
            ->will($this->returnValue('lus'));

        $this->ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::BEFORE_STORE, $this->isInstanceOf('Xi\Filelib\Event\StorageEvent'));

        $this->assertEquals('lus', $this->storage->store($resource, $path));
    }

    /**
     * @test
     */
    public function storeVersionDelegates()
    {
        $resource = Resource::create();
        $path = '/tenhunen/lipaisee.lus';
        $version = Version::get('lusso');

        $this->adapter
            ->expects($this->once())
            ->method('storeVersion')
            ->with($resource, $version, $path)
            ->will($this->returnValue('lus'));

        $this->ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::BEFORE_STORE, $this->isInstanceOf('Xi\Filelib\Event\StorageEvent'));

        $this->assertEquals('lus', $this->storage->storeVersion($resource, $version, $path));
    }

    /**
     * @test
     */
    public function deleteDelegates()
    {
        $resource = Resource::create();

        $this->adapter
            ->expects($this->once())
            ->method('delete')
            ->with($resource)
            ->will($this->returnValue('lus'));

        $this->adapter
            ->expects($this->once())
            ->method('exists')
            ->with($resource)
            ->will($this->returnValue(true));

        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with($resource);


        $this->assertEquals('lus', $this->storage->delete($resource));
    }

    /**
     * @test
     */
    public function deleteVersionDelegates()
    {
        $resource = Resource::create();
        $version = Version::get('lusso');

        $this->adapter
            ->expects($this->once())
            ->method('deleteVersion')
            ->with($resource, $version)
            ->will($this->returnValue('lus'));

        $this->adapter
            ->expects($this->once())
            ->method('versionExists')
            ->with($resource, $version)
            ->will($this->returnValue(true));

        $this->cache
            ->expects($this->once())
            ->method('deleteVersion')
            ->with($resource, $version);

        $this->assertEquals('lus', $this->storage->deleteVersion($resource, $version));
    }

    /**
     * @test
     */
    public function retrieveDelegates()
    {
        $resource = Resource::create();
        $retrieved = new Retrieved('lus', false);

        $this->adapter
            ->expects($this->once())
            ->method('retrieve')
            ->with($resource)
            ->will($this->returnValue($retrieved));

        $this->adapter
            ->expects($this->once())
            ->method('exists')
            ->with($resource)
            ->will($this->returnValue(true));

        $this->cache
            ->expects($this->at(0))
            ->method('get')
            ->with($resource)
            ->will($this->returnValue(false));

        $this->cache
            ->expects($this->at(1))
            ->method('set')
            ->with($resource, $retrieved);

        $this->assertEquals('lus', $this->storage->retrieve($resource));
    }

    /**
     * @test
     */
    public function retrieveExitsEarlyWithCache()
    {
        $resource = Resource::create();
        $retrieved = new Retrieved('lus', false);

        $this->adapter
            ->expects($this->never())
            ->method('retrieve');

        $this->adapter
            ->expects($this->never())
            ->method('exists');

        $this->cache
            ->expects($this->at(0))
            ->method('get')
            ->with($resource)
            ->will($this->returnValue($retrieved));

        $this->assertEquals('lus', $this->storage->retrieve($resource));
    }


    /**
     * @test
     */
    public function versionExistsDelegates()
    {
        $resource = Resource::create();

        $version = Version::get('lusso');

        $this->adapter
            ->expects($this->once())
            ->method('versionExists')
            ->with($resource, $version)
            ->will($this->returnValue('lus'));

        $this->assertEquals('lus', $this->storage->versionExists($resource, $version));
    }

    /**
     * @test
     */
    public function existsDelegates()
    {
        $resource = Resource::create();

        $this->adapter
            ->expects($this->once())
            ->method('exists')
            ->with($resource)
            ->will($this->returnValue('lus'));

        $this->assertEquals('lus', $this->storage->exists($resource));
    }

    /**
     * @test
     */
    public function retrieveVersionDelegates()
    {
        $resource = Resource::create();
        $version = Version::get('lusso');
        $retrieved = new Retrieved('lus', false);

        $this->adapter
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, $version)
            ->will($this->returnValue($retrieved));

        $this->adapter
            ->expects($this->once())
            ->method('versionExists')
            ->with($resource, $version)
            ->will($this->returnValue(true));

        $this->cache
            ->expects($this->at(0))
            ->method('getVersion')
            ->with($resource, $version)
            ->will($this->returnValue(false));

        $this->cache
            ->expects($this->at(1))
            ->method('setVersion')
            ->with($resource, $version, $retrieved);

        $this->assertEquals('lus', $this->storage->retrieveVersion($resource, $version));
    }

    /**
     * @test
     */
    public function retrieveVersionExitsEarlyWithCache()
    {
        $resource = Resource::create();
        $version = Version::get('lusso');
        $retrieved = new Retrieved('lus', false);

        $this->adapter
            ->expects($this->never())
            ->method('retrieveVersion');

        $this->adapter
            ->expects($this->never())
            ->method('versionExists');

        $this->cache
            ->expects($this->at(0))
            ->method('getVersion')
            ->with($resource, $version)
            ->will($this->returnValue($retrieved));

        $this->assertEquals('lus', $this->storage->retrieveVersion($resource, $version));
    }

}
