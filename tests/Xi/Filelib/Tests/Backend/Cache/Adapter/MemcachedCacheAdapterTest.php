<?php

namespace Xi\Filelib\Tests\Backend\Cache;

use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Backend\Cache\Adapter\MemcachedCacheAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Tests\TestCase;
use Memcached;

class MemcachedCacheAdapterTest extends TestCase
{
    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * @var MemcachedCacheAdapter
     */
    private $cache;

    public function setUp()
    {
        if (!class_exists('Memcached')) {
            return $this->markTestSkipped('Memcached not installed');
        }

        $this->memcached = new Memcached();
        $this->memcached->addServer('127.0.0.1', 11211);
        $this->cache = new MemcachedCacheAdapter($this->memcached, 'test___');
    }

    public function tearDown()
    {
        $this->memcached->flush();
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
            array(ConcreteResource::create(array('id' => 'xooxooxoo'))),
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
    public function deletes()
    {
        $identifiables = array(
            File::create(array('id' => 1, 'data' => array('lus' => 'hof'))),
            File::create(array('id' => 2, 'data' => array('lus' => 'hof'))),
            File::create(array('id' => 3, 'data' => array('lus' => 'hof'))),
        );

        foreach ($identifiables as $identifiable) {
            $this->cache->save($identifiable);
        }

        $keys = array();
        foreach ($identifiables as $identifiable) {
            $keys[] = $identifiable->getId();
        }

        $found = $this->cache->findByIds($keys, 'Xi\Filelib\File\File');
        $this->assertEquals(array_values($identifiables), array_values($found));

        $doNotDelete = array_shift($identifiables);

        foreach ($identifiables as $identifiable) {
            $this->cache->delete($identifiable);
        }

        $notGonnaFind = array_shift($identifiables);
        $this->assertFalse($this->cache->findById($notGonnaFind->getId(), get_class($notGonnaFind)));

        $this->assertEquals($doNotDelete, $this->cache->findById($doNotDelete->getId(), get_class($doNotDelete)));

        $this->cache->delete($doNotDelete);

        $this->assertFalse($this->cache->findById($doNotDelete->getId(), get_class($doNotDelete)));
    }

    /**
     * @test
     */
    public function clears()
    {
        $obj = File::create(['id' => 'xooooooooxers']);
        $this->cache->save($obj);

        $cached = $this->cache->findById($obj->getId(), get_class($obj));
        $this->assertEquals($obj, $cached);

        $this->cache->clear();

        $this->assertFalse($this->cache->findById($obj->getId(), get_class($obj)));
    }
}
