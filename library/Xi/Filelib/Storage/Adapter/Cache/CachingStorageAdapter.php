<?php

namespace Xi\Filelib\Storage\Adapter\Cache;

use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Tool\LazyReferenceResolver;
use Xi\Filelib\Version;
use Xi\Filelib\Versionable;

class CachingStorageAdapter implements StorageAdapter
{
    /**
     * @var LazyReferenceResolver
     */
    private $actualAdapter;

    /**
     * @var LazyReferenceResolver
     */
    private $cacheAdapter;

    /**
     * @param mixed $actualAdapter
     * @param mixed $cacheAdapter
     */
    public function __construct($actualAdapter, $cacheAdapter)
    {
        $this->actualAdapter = new LazyReferenceResolver($actualAdapter);
        $this->cacheAdapter = new LazyReferenceResolver($cacheAdapter);
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->actualAdapter->attachTo($filelib);
        $this->cacheAdapter->attachTo($filelib);
    }

    /**
     * Stores an uploaded file
     *
     * @param  Resource         $resource
     * @param  string           $tempResource
     * @throws FilelibException
     */
    public function store(Resource $resource, $tempResource)
    {
        $ret = $this->resolveCacheAdapter()->store($resource, $tempResource);
        $this->resolveActualAdapter()->store($resource, $tempResource);
        return $ret;
    }

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @return Retrieved
     * @throws FilelibException
     */
    public function retrieve(Resource $resource)
    {
        if ($this->resolveCacheAdapter()->exists($resource)) {
            return $this->resolveCacheAdapter()->retrieve($resource);
        }

        $ret = $this->resolveActualAdapter()->retrieve($resource);
        return $this->resolveCacheAdapter()->store($resource, $ret->getPath());
    }

    /**
     * Returns whether stored file exists
     *
     * @param  Resource $resource
     * @return boolean
     */
    public function exists(Resource $resource)
    {
        if ($this->resolveCacheAdapter()->exists($resource) === true) {
            return true;
        }

        return $this->resolveActualAdapter()->exists($resource);
    }

    /**
     * Deletes a file
     *
     * @param  Resource         $resource
     * @return boolean
     * @throws FilelibException
     */
    public function delete(Resource $resource)
    {
        if ($this->exists($resource)) {
            $this->resolveCacheAdapter()->delete($resource);
            return $this->resolveActualAdapter()->delete($resource);
        }
        return false;
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @param string $tempResource
     * @throws FileIOException
     */
    public function storeVersion(Versionable $versionable, Version $version, $tempResource)
    {
        $ret = $this->resolveCacheAdapter()->storeVersion($versionable, $version, $tempResource);
        $this->resolveActualAdapter()->storeVersion($versionable, $version, $tempResource);
        return $ret;
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return Retrieved
     * @throws FileIOException
     */
    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        if ($this->resolveCacheAdapter()->versionExists($versionable, $version)) {
            return $this->resolveCacheAdapter()->retrieveVersion($versionable, $version);
        }

        $ret = $this->resolveActualAdapter()->retrieveVersion($versionable, $version);
        return $this->resolveCacheAdapter()->storeVersion($versionable, $version, $ret->getPath());
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @throws FileIOException
     * @return bool
     */
    public function deleteVersion(Versionable $versionable, Version $version)
    {
        if ($this->versionExists($versionable, $version)) {
            $this->resolveCacheAdapter()->deleteVersion($versionable, $version);
            return $this->resolveActualAdapter()->deleteVersion($versionable, $version);
        }
        return false;
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @throws FileIOException
     * @return bool
     */
    public function versionExists(Versionable $versionable, Version $version)
    {

        if ($this->resolveCacheAdapter()->versionExists($versionable, $version) === true) {
            return true;
        }

        return $this->resolveActualAdapter()->versionExists($versionable, $version);
    }

    /**
     * @return StorageAdapter
     */
    private function resolveCacheAdapter()
    {
        return $this->cacheAdapter->resolve();
    }

    /**
     * @return StorageAdapter
     */
    private function resolveActualAdapter()
    {
        return $this->actualAdapter->resolve();
    }
}
