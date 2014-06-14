<?php

namespace Xi\Filelib\Storage;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Attacher;
use Xi\Filelib\Event\StorageEvent;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Exception;
use Xi\Filelib\Storage\FileIOException;

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

    public function __construct(StorageAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->eventDispatcher = $filelib->getEventDispatcher();
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
        if (!$this->exists($resource)) {
            throw new FileIOException("Physical file for resource #{$resource->getId()} does not exist");
        }

        $retrieved = $this->adapter->retrieve($resource);
        $event = new StorageEvent($retrieved);
        $this->eventDispatcher->dispatch(Events::AFTER_RETRIEVE, $event);

        return $retrieved;
    }

    public function delete(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException("Physical file for resource #{$resource->getId()} does not exist");
        }

        return $this->adapter->delete($resource);
    }

    public function store(Resource $resource, $tempFile)
    {
        try {
            $event = new StorageEvent($tempFile);
            $this->eventDispatcher->dispatch(Events::BEFORE_STORE, $event);
            return $this->adapter->store($resource, $tempFile);
        } catch (\Exception $e) {
            throw new FileIOException("Could not store physical file for resource #{$resource->getId()}", 500, $e);
        }
    }

    public function retrieveVersion(Storable $storable, $version)
    {
        if (!$this->versionExists($storable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($storable),
                    $storable->getId(),
                    $version
                )
            );
        }

        $retrieved = $this->adapter->retrieveVersion($storable, $version);
        $event = new StorageEvent($retrieved);
        $this->eventDispatcher->dispatch(Events::AFTER_RETRIEVE, $event);

        return $retrieved;
    }

    public function deleteVersion(Storable $storable, $version)
    {
        if (!$this->versionExists($storable, $version)) {
            throw new FileIOException(
                sprintf(
                    "Physical file for storable of class '%s', #%s, version '%s' does not exist",
                    get_class($storable),
                    $storable->getId(),
                    $version
                )
            );
        }

        return $this->adapter->deleteVersion($storable, $version);
    }

    public function storeVersion(Storable $storable, $version, $tempFile)
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
                    $version
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
     * @param string $version
     * @return bool
     */
    public function versionExists(Storable $storable, $version)
    {
        return $this->adapter->versionExists($storable, $version);
    }
}
