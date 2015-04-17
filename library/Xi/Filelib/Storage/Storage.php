<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Attacher;
use Xi\Filelib\Event\StorageEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Tool\LazyReferenceResolver;
use Xi\Filelib\Version;
use Xi\Filelib\Versionable;

class Storage implements Attacher
{
    /**
     * @var LazyReferenceResolver
     */
    private $adapter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $cache;

    public function __construct($adapter, RetrievedCache $cache = null)
    {
        $this->adapter = new LazyReferenceResolver($adapter, 'Xi\Filelib\Storage\Adapter\StorageAdapter');

        if (!$cache) {
            $cache = new RetrievedCache();
        }
        $this->cache = $cache;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->adapter->attachTo($filelib);
    }

    /**
     * @return LazyReferenceResolver
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function retrieve(Resource $resource)
    {
        if ($retrieved = $this->cache->get($resource)) {
            return $retrieved->getPath();
        }

        if (!$this->exists($resource)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for resource #%s does not exist",
                    $resource->getId()
                )
            );
        }
        $retrieved = $this->getResolvedAdapter()->retrieve($resource);

        $event = new StorageEvent($retrieved);
        $this->eventDispatcher->dispatch(Events::AFTER_RETRIEVE, $event);

        $this->cache->set($resource, $retrieved);
        return $retrieved->getPath();
    }

    public function delete(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for resource #%s does not exist",
                    $resource->getId()
                )
            );
        }

        $this->cache->delete($resource);
        return $this->getResolvedAdapter()->delete($resource);
    }

    public function store(Resource $resource, $tempFile)
    {
        try {
            $event = new StorageEvent($tempFile);
            $this->eventDispatcher->dispatch(Events::BEFORE_STORE, $event);
            return $this->getResolvedAdapter()->store($resource, $tempFile);
        } catch (\Exception $e) {
            throw new FileIOException(
                sprintf(
                    "Could not store physical file for resource #%s",
                    $resource->getId()
                ),
                500,
                $e
            );
        }
    }

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        if ($retrieved = $this->cache->getVersion($versionable, $version)) {
            return $retrieved->getPath();
        }

        if (!$this->versionExists($versionable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($versionable),
                    $versionable->getId(),
                    $version->toString()
                )
            );
        }

        $retrieved = $this->getResolvedAdapter()->retrieveVersion($versionable, $version);
        $event = new StorageEvent($retrieved);
        $this->eventDispatcher->dispatch(Events::AFTER_RETRIEVE, $event);

        $this->cache->setVersion($versionable, $version, $retrieved);
        return $retrieved->getPath();
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        if (!$this->versionExists($versionable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($versionable),
                    $versionable->getId(),
                    $version->toString()
                )
            );
        }

        $this->cache->deleteVersion($versionable, $version);
        return $this->getResolvedAdapter()->deleteVersion($versionable, $version);
    }

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        try {
            $event = new StorageEvent($tempFile);
            $this->eventDispatcher->dispatch(Events::BEFORE_STORE, $event);
            return $this->getResolvedAdapter()->storeVersion($versionable, $version, $tempFile);
        } catch (\Exception $e) {

            throw new FileIOException(
                sprintf(
                    "Could not store physical file for storable of class '%s', #%s, version '%s'",
                    get_class($versionable),
                    $versionable->getId(),
                    $version->toString()
                ),
                0,
                $e
            );
        }
    }

    /**
     * @param Resource $resource
     * @return bool
     */
    public function exists(Resource $resource)
    {
        return $this->getResolvedAdapter()->exists($resource);
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return bool
     */
    public function versionExists(Versionable $versionable, Version $version)
    {
        return $this->getResolvedAdapter()->versionExists($versionable, $version);
    }

    /**
     * @return StorageAdapter
     */
    private function getResolvedAdapter()
    {
        return $this->adapter->resolve();
    }
}
