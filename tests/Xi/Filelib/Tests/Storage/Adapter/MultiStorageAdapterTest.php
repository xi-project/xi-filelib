<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Storage\Adapter\MultiStorageAdapter;
use Xi\Filelib\Resource\Resource;

/**
 * @group storage
 */
class MultiStorageAdapterTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var MultiStorageAdapter
     */
    protected $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $mockStorages = array();

    protected $resource;

    public function setUp()
    {
        $mockStorage = $this->getMockedStorageAdapter();
        $mockStorage2 = $this->getMockedStorageAdapter();

        $this->mockStorages[] = $mockStorage;
        $this->mockStorages[] = $mockStorage2;

        $multiStorage = new MultiStorageAdapter();
        $multiStorage->addStorage($mockStorage);
        $multiStorage->addStorage($mockStorage2);

        $this->storage = $multiStorage;
        $this->resource = Resource::create(array('id' => 1));
        $this->version = Version::get('xoo');

    }

    /**
     * @test
     */
    public function deleteShouldIterateAllInnerStorages()
    {

        foreach ($this->mockStorages as $storage) {
            $storage->expects($this->exactly(1))
             ->method('delete')
             ->will($this->returnValue('1'));
        }

        $this->storage->delete($this->resource);
    }

    /**
     * @test
     */
    public function deleteVersionShouldIterateAllInnerStorages()
    {
        foreach ($this->mockStorages as $storage) {
            $storage->expects($this->exactly(1))
             ->method('deleteVersion')
             ->will($this->returnValue('1'));
        }

        $this->storage->deleteVersion($this->resource, $this->version);

    }

    /**
     * @test
     */
    public function storeShouldIterateAllInnerStorages()
    {
        foreach ($this->mockStorages as $storage) {
            $storage->expects($this->exactly(1))
             ->method('store')
             ->will($this->returnValue('1'));
        }

        $this->storage->store($this->resource, 'puuppapath');

    }

    /**
     * @test
     */
    public function storeVersionShouldIterateAllInnerStorages()
    {
        foreach ($this->mockStorages as $storage) {
            $storage->expects($this->exactly(1))
             ->method('storeVersion')
             ->will($this->returnValue('1'));
        }

        $this->storage->storeVersion($this->resource, $this->version, 'puuppapath');

    }

    /**
     * @test
     *
     */
    public function sessionStorageShouldInitializeRandomlyAndAlwaysReturnTheSameStorage()
    {
        $sessionStorage = $this->storage->getSessionStorage();

        $this->assertInstanceOf('Xi\Filelib\Storage\Adapter\StorageAdapter', $sessionStorage);

        for ($x = 1; $x <= 10; $x++) {
            $this->assertEquals($sessionStorage, $this->storage->getSessionStorage());
        }
    }

    /**
     * @test
     *
     */
    public function sessionStorageShouldObeySessionStorageIdSetterAndAlwaysReturnIt()
    {
        $this->storage->setSessionStorageId(1);

        $this->assertEquals(1, $this->storage->getSessionStorageId());

        $sessionStorage = $this->storage->getSessionStorage();

        $this->assertEquals($this->mockStorages[1], $this->storage->getSessionStorage());
        $this->assertEquals($this->mockStorages[1], $this->storage->getSessionStorage());
    }

    /**
     * @test
     * @expectedException Xi\Filelib\LogicException
     */
    public function whenNoStoragesGetSessionStorageShouldThrowException()
    {
        $storage = new MultiStorageAdapter();

        $sessionStorage = $storage->getSessionStorage();
    }

    /**
     * @test
     */
    public function retrieveVersionShouldDelegateToSessionStorage()
    {
        $this->storage->setSessionStorageId(1);

        $this->mockStorages[0]->expects($this->exactly(0))
             ->method('retrieveVersion')
             ->will($this->returnValue('1'));

        $this->mockStorages[1]->expects($this->exactly(1))
             ->method('retrieveVersion')
             ->will($this->returnValue('1'));

        $this->storage->retrieveVersion($this->resource, $this->version);
    }

    /**
     * @test
     */
    public function retrieveShouldDelegateToSessionStorage()
    {
        $this->storage->setSessionStorageId(1);

        $this->mockStorages[0]->expects($this->exactly(0))
             ->method('retrieve')
             ->will($this->returnValue('1'));

        $this->mockStorages[1]->expects($this->exactly(1))
             ->method('retrieve')
             ->will($this->returnValue('1'));

        $this->storage->retrieve($this->resource);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\InvalidArgumentException
     */
    public function addStorageShouldThrowExceptionWhenAddingMultiStorage()
    {
        $this->storage->addStorage(new MultiStorageAdapter());
    }

    /**
     * @test
     */
    public function existsShouldDelegateToSessionStorage()
    {
        $this->storage->setSessionStorageId(1);

        $this->mockStorages[0]->expects($this->never())
            ->method('exists');

        $this->mockStorages[1]->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $this->storage->exists($this->resource, $this->version);
    }

    /**
     * @test
     */
    public function versionExistsShouldDelegateToSessionStorage()
    {
        $this->storage->setSessionStorageId(1);

        $this->mockStorages[0]->expects($this->never())
            ->method('versionExists');

        $this->mockStorages[1]->expects($this->once())
            ->method('versionExists')
            ->will($this->returnValue(true));

        $this->storage->versionExists($this->resource, $this->version);
    }

}
