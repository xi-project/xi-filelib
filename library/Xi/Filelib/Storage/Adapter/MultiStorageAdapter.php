<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\LogicException;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Storage\Storable;
use Xi\Filelib\Plugin\VersionProvider\Version;

class MultiStorageAdapter implements StorageAdapter
{
    /**
     * @var StorageAdapter[]
     */
    private $storages = array();

    /**
     * @var integer Session storage id for fetch operations
     */
    private $sessionStorageId;

    public function attachTo(FileLibrary $filelib)
    {
    }

    public function addStorage(StorageAdapter $storage)
    {
        if ($storage instanceof MultiStorageAdapter) {
            throw new InvalidArgumentException('MultiStorage cannot contain a MultiStorage');
        }

        $this->storages[] = $storage;
    }

    /**
     * Returns an array of inner storages
     *
     * @return StorageAdapter[]
     */
    public function getStorages()
    {
        return $this->storages;
    }

    /**
     *
     * @param int $sessionStorage Set session storage
     */
    public function setSessionStorageId($sessionStorageId)
    {
        $this->sessionStorageId = $sessionStorageId;
    }

    public function getSessionStorageId()
    {
        return $this->sessionStorageId;
    }

    /**
     * Returns session storage
     *
     * @return StorageAdapter
     */
    public function getSessionStorage()
    {
        if (!$this->storages) {
            throw new LogicException('MultiStorage has no inner storages. Can not get session storage.');
        }

        $sessionStorageId = $this->getSessionStorageId();

        if ($sessionStorageId === null) {
            $sessionStorageId = array_rand($this->storages);
            $this->setSessionStorageId($sessionStorageId);
        }

        return $this->storages[$this->getSessionStorageId()];
    }

    public function store(Resource $resource, $tempFile)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->store($resource, $tempFile);
        }
    }

    public function storeVersion(Storable $storable, Version $version, $tempFile)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->storeVersion($storable, $version, $tempFile);
        }
    }

    public function retrieve(Resource $resource)
    {
        return $this->getSessionStorage()->retrieve($resource);
    }

    public function retrieveVersion(Storable $storable, Version $version)
    {
        return $this->getSessionStorage()->retrieveVersion($storable, $version);
    }

    public function delete(Resource $resource)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->delete($resource);
        }
    }

    public function deleteVersion(Storable $storable, Version $version)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->deleteVersion($storable, $version);
        }
    }

    public function exists(Resource $resource)
    {
        return $this->getSessionStorage()->exists($resource);
    }

    public function versionExists(Storable $storable, Version $version)
    {
        return $this->getSessionStorage()->versionExists($storable, $version);
    }
}
