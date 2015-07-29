<?php

namespace Xi\Filelib\Storage\Adapter\Cache;

use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Tool\LazyReferenceResolver;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

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
        if ($this->resolveCacheAdapter()->exists($resource) === true) {
            $this->resolveCacheAdapter()->delete($resource);
        }

        return $this->resolveActualAdapter()->delete($resource);
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
