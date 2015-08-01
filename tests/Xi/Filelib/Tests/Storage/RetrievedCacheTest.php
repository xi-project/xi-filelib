<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\File\File;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\RetrievedCache;
use Xi\Filelib\Tests\TestCase;

class RetrievedCacheTest extends TestCase
{
    /**
     * @var RetrievedCache
     */
    private $cache;

    /**
     * @var Retrieved
     */
    private $retrieved;

    /**
     * @var Retrieved
     */
    private $retrieved2;

    public function setUp()
    {
        $this->cache = new RetrievedCache();
        $this->retrieved = new Retrieved('lussogrande', false);
        $this->retrieved2 = new Retrieved('lussogrande', false);
    }

    /**
     * @test
     */
    public function caches()
    {
        $id = 'lusso';
        $resource = ConcreteResource::create(array('id' => $id));

        $this->assertFalse($this->cache->get($resource));
        $this->cache->set($resource, $this->retrieved);
        $this->assertSame($this->retrieved, $this->cache->get($resource));
        $this->cache->delete($resource);
        $this->assertFalse($this->cache->get($resource));
    }
}
