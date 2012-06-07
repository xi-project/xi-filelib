<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\FilelibException;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;

class MultiStorage extends AbstractStorage implements Storage
{
    /**
     * @var array
     */
    private $storages = array();

    /**
     * @var integer Session storage id for fetch operations
     */
    private $sessionStorageId;

    public function addStorage(Storage $storage)
    {
        if ($storage instanceof MultiStorage) {
            throw new FilelibException('MultiStorage cannot contain a MultiStorage');
        }

        $this->storages[] = $storage;
    }

    /**
     * Returns an array of inner storages
     *
     * @return array
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
     * @return Storage
     */
    public function getSessionStorage()
    {
        if (!$this->storages) {
            throw new FilelibException('MultiStorage has no inner storages. Can not get session storage.');
        }

        if (!$sessionStorageId = $this->getSessionStorageId()) {
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

    public function storeVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->storeVersion($resource, $version, $tempFile);
        }
    }

    public function retrieve(Resource $resource)
    {
        return $this->getSessionStorage()->retrieve($resource);
    }

    public function retrieveVersion(Resource $resource, $version, File $file = null)
    {
        return $this->getSessionStorage()->retrieveVersion($resource, $version);
    }

    public function delete(Resource $resource)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->delete($resource);
        }
    }

    public function deleteVersion(Resource $resource, $version, File $file = null)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->deleteVersion($resource, $version);
        }
    }

}
