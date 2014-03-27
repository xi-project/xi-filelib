<?php

namespace Xi\Filelib\Tests\Cache;

use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Cache\Cache;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\File;

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
            return $this->markTestSkipped('Memcached required');
        }

        $this->adapter = $this->getMockedCacheAdapter();
        $this->cache = new Cache($this->adapter);
    }

    /**
     * @test
     */
    public function saveManyDelegates()
    {
        $arr = array(
            File::create(array())
        );

        $this->adapter
            ->expects($this->once())
            ->method('saveMany')
            ->with($arr)
            ->will($this->returnValue('xoo'));


        $ret = $this->cache->saveMany($arr);
        $this->assertEquals('xoo', $ret);
    }

    /**
     * @test
     */
    public function deleteManyDelegates()
    {
        $arr = array(
            File::create(array())
        );

        $this->adapter
            ->expects($this->once())
            ->method('deleteMany')
            ->with($arr)
            ->will($this->returnValue('xoo'));


        $ret = $this->cache->deleteMany($arr);
        $this->assertEquals('xoo', $ret);
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

}
