<?php

namespace Xi\Filelib\Tests\Cache;

use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Cache\Cache;
use Xi\Filelib\Cache\MemcachedCache;
use Xi\Filelib\File\File;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\Tests\TestCase;
use Memcached;

class MemcachedCacheTest extends TestCase
{
    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * @var Cache
     */
    private $cache;

    public function setUp()
    {
        $this->memcached = new Memcached();
        $this->memcached->addServer('127.0.0.1', 11211);
        $this->cache = new MemcachedCache($this->memcached, 'test___');
    }

    public function tearDown()
    {
        $this->memcached->flush();
        $this->memcached->quit();
    }


    /**
     * @test
     */
    public function exists()
    {
        $this->assertClassExists('Xi\Filelib\Cache\MemcachedCache');
        $this->assertImplements(
            'Xi\Filelib\Cache\Cache',
            'Xi\Filelib\Cache\MemcachedCache'
        );
    }

    /**
     * @test
     */
    public function failsToSaveNonIdentifiable()
    {
        $this->setExpectedException('Xi\Filelib\RuntimeException');

        $file = File::create();
        $this->cache->save($file);
    }

    /**
     * @test
     */
    public function saves()
    {
        $file = File::create(array('id' => 1));
        $file->setData(array('lus' => 'hof'));
        $this->cache->save($file);
    }

    public function provideIdentifiables()
    {
        return array(
            array(File::create(array('id' => 1, 'data' => array('lus' => 'hof')))),
            array(Folder::create(array('id' => 'xooxooxoo'))),
            array(Resource::create(array('id' => 'xooxooxoo'))),
        );
    }

    /**
     * @test
     * @dataProvider provideIdentifiables
     */
    public function finds(Identifiable $obj)
    {
        $this->cache->save($obj);

        $cached = $this->cache->findById($obj->getId(), get_class($obj));
        $this->assertEquals($obj, $cached);
    }

    /**
     * @test
     */
    public function savesManyAndFindsOne()
    {
        $identifiables = array(
            File::create(array('id' => 1, 'data' => array('lus' => 'hof'))),
            Folder::create(array('id' => 'xooxooxoo')),
            Resource::create(array('id' => 'xooxooxoo')),
        );

        $this->cache->saveMany($identifiables);

        $cached = $this->cache->findById('xooxooxoo', 'Xi\Filelib\Folder\Folder');
        $this->assertEquals($identifiables[1], $cached);
    }


}
