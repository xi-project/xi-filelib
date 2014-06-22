<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Attacher;
use Xi\Filelib\Event\StorageEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Exception;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Version;

class Storage implements Attacher
{
    /**
     * @var StorageAdapter
     */
    private $adapter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $cache;

    public function __construct(StorageAdapter $adapter, RetrievedCache $cache = null)
    {
        $this->adapter = $adapter;

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
     * @return StorageAdapter
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

        $retrieved = $this->adapter->retrieve($resource);
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
        return $this->adapter->delete($resource);
    }

    public function store(Resource $resource, $tempFile)
    {
        try {
            $event = new StorageEvent($tempFile);
            $this->eventDispatcher->dispatch(Events::BEFORE_STORE, $event);
            return $this->adapter->store($resource, $tempFile);
        } catch (\Exception $e) {
            throw new FileIOException(
                sprintf(
                    "Could not store physical file for resource #%s",
                    $resource->getId()
                )
                , 500,
                $e
            );
        }
    }

    public function retrieveVersion(Storable $storable, Version $version)
    {
        if ($retrieved = $this->cache->getVersion($storable, $version)) {
            return $retrieved->getPath();
        }

        if (!$this->versionExists($storable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($storable),
                    $storable->getId(),
                    $version->toString()
                )
            );
        }

        $retrieved = $this->adapter->retrieveVersion($storable, $version);
        $event = new StorageEvent($retrieved);
        $this->eventDispatcher->dispatch(Events::AFTER_RETRIEVE, $event);

        $this->cache->setVersion($storable, $version, $retrieved);
        return $retrieved->getPath();
    }

    public function deleteVersion(Storable $storable, Version $version)
    {
        if (!$this->versionExists($storable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($storable),
                    $storable->getId(),
                    $version->toString()
                )
            );
        }

        $this->cache->deleteVersion($storable, $version);
        return $this->adapter->deleteVersion($storable, $version);
    }

    public function storeVersion(Storable $storable, Version $version, $tempFile)
    {
        try {
            $event = new StorageEvent($tempFile);
            $this->eventDispatcher->dispatch(Events::BEFORE_STORE, $event);
            return $this->adapter->storeVersion($storable, $version, $tempFile);
        } catch (\Exception $e) {

            throw new FileIOException(
                sprintf(
                    "Could not store physical file for storable of class '%s', #%s, version '%s'",
                    get_class($storable),
                    $storable->getId(),
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
        return $this->adapter->exists($resource);
    }

    /**
     * @param Storable $storable
     * @param Version $version
     * @return bool
     */
    public function versionExists(Storable $storable, Version $version)
    {
        return $this->adapter->versionExists($storable, $version);
    }
}
