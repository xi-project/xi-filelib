<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;

/**
 * Storage interface
 *
 * @author pekkis
 *
 */
interface Storage
{
    /**
     * Stores an uploaded file
     *
     * @param Resource $resource
     * @param string $tempResource
     * @throws FilelibException
     */
    public function store(Resource $resource, $tempResource);

    /**
     * Stores a version of a file
     *
     * @param Resource $resource
     * @param string $version
     * @param string $tempResource Resource to be stored
     * @throws FilelibException
     */
    public function storeVersion(Resource $resource, $version, $tempResource);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @return Resource
     */
    public function retrieve(Resource $resource);

    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @param string $version
     * @return Resource
     */
    public function retrieveVersion(Resource $resource, $version);

    /**
     * Deletes a file
     *
     * @param Resource $resource
     * @return boolean
     */
    public function delete(Resource $resource);

    /**
     * Deletes a version of a file
     *
     * @param Resource $resource
     * @param string $version
     */
    public function deleteVersion(Resource $resource, $version);

}
