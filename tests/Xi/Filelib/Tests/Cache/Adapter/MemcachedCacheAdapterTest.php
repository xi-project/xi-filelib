<?php

namespace Xi\Filelib\Tests\Cache;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Cache\Adapter\MemcachedCacheAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\Tests\TestCase;
use Memcached;

class MemcachedCacheAdapterTest extends TestCase
{
    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * @var MemcachedCache
     */
    private $cache;

    public function setUp()
    {
        $this->memcached = new Memcached();
        $this->memcached->addServer('127.0.0.1', 11211);
        $this->cache = new MemcachedCacheAdapter($this->memcached, 'test___');
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
        $this->assertClassExists('Xi\Filelib\Cache\Adapter\MemcachedCacheAdapter');
        $this->assertImplements(
            'Xi\Filelib\Cache\Adapter\CacheAdapter',
            'Xi\Filelib\Cache\Adapter\MemcachedCacheAdapter'
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
    public function returnsFalseWhenNotFound()
    {
        $this->assertFalse(
            $this->cache->findById('xooxer', 'Xi\Filelib\File\File')
        );
    }

    /**
     * @test
     */
    public function returnsEmptyArrayWhenNotFound()
    {
        $this->assertEquals(
            array(),
            $this->cache->findByIds(array('lusso', 'tusso'), 'Xi\Filelib\File\File')
        );
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

    /**
     * @test
     */
    public function deletes()
    {
        $identifiables = array(
            File::create(array('id' => 1, 'data' => array('lus' => 'hof'))),
            File::create(array('id' => 2, 'data' => array('lus' => 'hof'))),
            File::create(array('id' => 3, 'data' => array('lus' => 'hof'))),
        );

        $this->cache->saveMany($identifiables);

        $keys = array();
        foreach ($identifiables as $identifiable) {
            $keys[] = $identifiable->getId();
        }

        $found = $this->cache->findByIds($keys, 'Xi\Filelib\File\File');
        $this->assertEquals(array_values($identifiables), array_values($found));

        $doNotDelete = array_shift($identifiables);
        $this->cache->deleteMany($identifiables);

        $notGonnaFind = array_shift($identifiables);
        $this->assertFalse($this->cache->findById($notGonnaFind->getId(), get_class($notGonnaFind)));

        $this->assertEquals($doNotDelete, $this->cache->findById($doNotDelete->getId(), get_class($doNotDelete)));

        $this->cache->delete($doNotDelete);

        $this->assertFalse($this->cache->findById($doNotDelete->getId(), get_class($doNotDelete)));
    }
}
