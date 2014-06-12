<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Storage\Storable;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\File\FileObject;

/**
 * Storage interface
 *
 */
interface StorageAdapter
{
    /**
     * Stores an uploaded file
     *
     * @param  Resource         $resource
     * @param  string           $tempResource
     * @throws FilelibException
     */
    public function store(Resource $resource, $tempResource);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @return string
     * @throws FilelibException
     */
    public function retrieve(Resource $resource);

    /**
     * Returns whether stored file exists
     *
     * @param  Resource $resource
     * @return boolean
     */
    public function exists(Resource $resource);

    /**
     * Deletes a file
     *
     * @param  Resource         $resource
     * @return boolean
     * @throws FilelibException
     */
    public function delete(Resource $resource);

    /**
     * @param Storable $storable
     * @param string $version
     * @param string $tempResource
     * @throws FileIOException
     */
    public function storeVersion(Storable $storable, $version, $tempResource);

    /**
     * @param Storable $storable
     * @param string $version
     * @throws FileIOException
     */
    public function retrieveVersion(Storable $storable, $version);

    /**
     * @param Storable $storable
     * @param string $version
     * @throws FileIOException
     */
    public function deleteVersion(Storable $storable, $version);

    /**
     * @param Storable $storable
     * @param string $version
     * @throws FileIOException
     */
    public function versionExists(Storable $storable, $version);
}
