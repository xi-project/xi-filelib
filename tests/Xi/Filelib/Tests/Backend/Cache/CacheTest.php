<?php

namespace Xi\Filelib\Tests\Backend\Cache;

use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\File;
use Xi\Filelib\Events;

class CacheTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var Cache
     */
    private $cache;

    public function setUp()
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached required');
            return;
        }

        $this->adapter = $this->getMockedCacheAdapter();
        $this->cache = new Cache($this->adapter);
    }

    /**
     * @test
     */
    public function findManyDelegates()
    {
        $arr = array(
            'tus', 'lus'
        );
        $class = 'Xi\Filelib\File\File';

        $file = File::create();

        $this->adapter
            ->expects($this->once())
            ->method('findByIds')
            ->with($arr, $class)
            ->will($this->returnValue(array($file)));

        $request = new FindByIdsRequest($arr, $class);
        $ret = $this->cache->findByIds($request);

        $this->assertEquals(new \ArrayIterator(array($file)), $ret->getResult());
    }

    /**
     * @test
     */
    public function findDelegates()
    {
        $id = 'lustus';
        $class = 'Xi\Filelib\File\File';

        $this->adapter
            ->expects($this->once())
            ->method('findById')
            ->with($id, $class)
            ->will($this->returnValue('xoo'));

        $ret = $this->cache->findById($id, $class);
        $this->assertEquals('xoo', $ret);
    }

    /**
     * @test
     */
    public function saveDelegates()
    {
        $identifiable = File::create();

        $this->adapter
            ->expects($this->once())
            ->method('save')
            ->with($identifiable)
            ->will($this->returnValue('xoo'));

        $ret = $this->cache->save($identifiable);
        $this->assertEquals('xoo', $ret);
    }

    /**
     * @test
     */
    public function deleteDelegates()
    {
        $identifiable = File::create();

        $this->adapter
            ->expects($this->once())
            ->method('delete')
            ->with($identifiable)
            ->will($this->returnValue('xoo'));

        $ret = $this->cache->delete($identifiable);
        $this->assertEquals('xoo', $ret);
    }

    /**
     * @test
     */
    public function subscribesToCorrectEvents()
    {
        $this->assertEquals(
            array(
                Events::FILE_AFTER_CREATE,
                Events::FILE_AFTER_UPDATE,
                Events::FILE_AFTER_DELETE,
                Events::FOLDER_AFTER_CREATE,
                Events::FOLDER_AFTER_UPDATE,
                Events::FOLDER_AFTER_DELETE,
                Events::RESOURCE_AFTER_CREATE,
                Events::RESOURCE_AFTER_UPDATE,
                Events::RESOURCE_AFTER_DELETE,
                Events::IDENTIFIABLE_INSTANTIATE,
            ),
            array_keys(Cache::getSubscribedEvents())
        );
    }


}
