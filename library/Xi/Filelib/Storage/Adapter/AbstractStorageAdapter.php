<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Exception;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Storage\Storable;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class AbstractStorageAdapter implements StorageAdapter
{
    abstract protected function doRetrieve(Resource $resource);

    abstract protected function doRetrieveVersion(Storable $storable, $version);

    abstract protected function doStore(Resource $resource, $tempFile);

    abstract protected function doStoreVersion(Storable $storable, $version, $tempFile);

    abstract protected function doDelete(Resource $resource);

    abstract protected function doDeleteVersion(Storable $storable, $version);

    public function retrieve(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException("Physical file for resource #{$resource->getId()} does not exist");
        }

        $retrieved = $this->doRetrieve($resource);
        return $retrieved;
    }

    public function delete(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException("Physical file for resource #{$resource->getId()} does not exist");
        }

        return $this->doDelete($resource);
    }

    public function store(Resource $resource, $tempFile)
    {
        try {
            return $this->doStore($resource, $tempFile);
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
        $retrieved = $this->doRetrieveVersion($storable, $version);
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

        return $this->doDeleteVersion($storable, $version);
    }

    public function storeVersion(Storable $storable, $version, $tempFile)
    {
        try {
            return $this->doStoreVersion($storable, $version, $tempFile);
        } catch (Exception $e) {
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
     * @param Storable $storable
     * @return array Tuple of storage and file (or null)
     */
    protected function extractResourceAndFileFromStorable(Storable $storable)
    {
        if ($storable instanceof File) {
            $file = $storable;
            $resource = $file->getResource();
        } else {
            $resource = $storable;
            $file = null;
        }
        return array($resource, $file);
    }
}
