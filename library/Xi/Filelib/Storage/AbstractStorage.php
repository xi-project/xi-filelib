<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Storage\Storage;
use Exception;
use Xi\Filelib\InvalidArgumentException;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class AbstractStorage implements Storage
{
    abstract protected function doRetrieve(Resource $resource);

    abstract protected function doRetrieveVersion(Resource $resource, $version, File $file = null);

    abstract protected function doStore(Resource $resource, $tempFile);

    abstract protected function doStoreVersion(Resource $resource, $version, $tempFile, File $file = null);

    abstract protected function doDelete(Resource $resource);

    abstract protected function doDeleteVersion(Resource $resource, $version, File $file = null);

    public function retrieve(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException("File for resource #{$resource->getId()} does not exist");
        }

        $retrieved = $this->doRetrieve($resource);
        return $retrieved;
    }

    public function retrieveVersion(Resource $resource, $version, File $file = null)
    {
        if (!$this->versionExists($resource, $version, $file)) {
            throw new FileIOException("File version '{$version}' for resource #{$resource->getId()} does not exist");
        }

        $retrieved = $this->doRetrieveVersion($resource, $version, $file);
        return $retrieved;
    }

    public function delete(Resource $resource)
    {
        if (!$this->exists($resource)) {
            throw new FileIOException("File for resource #{$resource->getId()} does not exist");
        }

        return $this->doDelete($resource);
    }

    public function deleteVersion(Resource $resource, $version, File $file = null)
    {
        if (!$this->versionExists($resource, $version, $file)) {
            throw new FileIOException("File version '{$version}' for resource #{$resource->getId()} does not exist");
        }

        return $this->doDeleteVersion($resource, $version, $file);
    }

    public function store(Resource $resource, $tempFile)
    {
        try {
            return $this->doStore($resource, $tempFile);
        } catch (\Exception $e) {
            throw new FileIOException("Could not store file for resource #{$resource->getId()}", 500, $e);
        }
    }

    public function storeVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        try {
            return $this->doStoreVersion($resource, $version, $tempFile, $file);
        } catch (\Exception $e) {
            throw new FileIOException(
                "Could not store file version '{$version}' for resource #{$resource->getId()}",
                500,
                $e
            );
        }
    }
}
