<?php

namespace Xi\Filelib\Tests\Backend\Cache;

use Xi\Filelib\Backend\Cache\Adapter\NullCacheAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Tests\TestCase;

class NullCacheAdapterTest extends TestCase
{
    /**
     * @var NullCacheAdapter
     */
    private $cache;

    public function setUp()
    {
        $this->cache = new NullCacheAdapter();
    }

    /**
     * @test
     */
    public function saves()
    {
        $file = File::create(array('id' => 1));
        $this->assertNull($this->cache->save($file));
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
    public function doesntFind(Identifiable $obj)
    {
        $cached = $this->cache->findById($obj->getId(), get_class($obj));
        $this->assertFalse($cached);
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
        $file = File::create(['id' => 'xooxer']);
        $this->assertNull($this->cache->delete($file));
    }

    /**
     * @test
     */
    public function clears()
    {
        $this->assertNull($this->cache->clear());
    }

}
