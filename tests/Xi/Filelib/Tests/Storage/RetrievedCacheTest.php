<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\File\File;
use Xi\Filelib\Versionable\Version;
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
        $versionable = File::create(array('id' => $id));
        $versionable2 = Resource::create(array('id' => $id));

        $this->assertFalse($this->cache->getVersion($versionable, $version));

        $this->cache->setVersion($versionable, $version, $this->retrieved);
        $this->cache->setVersion($versionable2, $version, $this->retrieved2);

        $this->assertSame($this->retrieved, $this->cache->getVersion($versionable, $version));
        $this->assertSame($this->retrieved2, $this->cache->getVersion($versionable2, $version));

        $this->assertNotSame(
            $this->cache->getVersion($versionable, $version),
            $this->cache->getVersion($versionable2, $version)
        );

        $this->cache->deleteVersion($versionable, $version);
        $this->assertFalse($this->cache->getVersion($versionable, $version));
        $this->assertSame($this->retrieved2, $this->cache->getVersion($versionable2, $version));
    }
}
