<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Resource\Resource;
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
        $resource = Resource::create(array('id' => $id));

        $this->assertFalse($this->cache->get($resource));
        $this->cache->set($resource, $this->retrieved);
        $this->assertSame($this->retrieved, $this->cache->get($resource));
        $this->cache->delete($resource);
        $this->assertFalse($this->cache->get($resource));
    }

    /**
     * @test
     */
    public function cachesVersions()
    {
        $id = 'tusso con lusso';
        $version = Version::get('tenhunizer');
        $storable = File::create(array('id' => $id));
        $storable2 = Resource::create(array('id' => $id));

        $this->assertFalse($this->cache->getVersion($storable, $version));

        $this->cache->setVersion($storable, $version, $this->retrieved);
        $this->cache->setVersion($storable2, $version, $this->retrieved2);

        $this->assertSame($this->retrieved, $this->cache->getVersion($storable, $version));
        $this->assertSame($this->retrieved2, $this->cache->getVersion($storable2, $version));

        $this->assertNotSame(
            $this->cache->getVersion($storable, $version),
            $this->cache->getVersion($storable2, $version)
        );

        $this->cache->deleteVersion($storable, $version);
        $this->assertFalse($this->cache->getVersion($storable, $version));
        $this->assertSame($this->retrieved2, $this->cache->getVersion($storable2, $version));
    }
}
