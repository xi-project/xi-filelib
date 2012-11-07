<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Exception\FilelibException;
use Xi\Filelib\File\FileObject;

/**
 * Storage interface
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
     * @param null|File $file
     * @throws FilelibException
     */
    public function storeVersion(Resource $resource, $version, $tempResource, File $file = null);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @return FileObject
     * @throws FilelibException
     */
    public function retrieve(Resource $resource);

    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @param string $version
     * @param null|File $file
     * @return FileObject
     * @throws FilelibException
     *
     */
    public function retrieveVersion(Resource $resource, $version, File $file = null);

    /**
     * Deletes a file
     *
     * @param Resource $resource
     * @return boolean
     * @throws FilelibException
     */
    public function delete(Resource $resource);

    /**
     * Deletes a version of a file
     *
     * @param Resource $resource
     * @param string $version
     * @param null|File $file
     * @throws FilelibException
     */
    public function deleteVersion(Resource $resource, $version, File $file = null);


    /**
     * Returns whether stored file exists
     *
     * @param Resource $resource
     * @return boolean
     */
    public function exists(Resource $resource);

    /**
     * Returns whether a stored version file exists
     *
     * @param Resource $resource
     * @param $version
     * @param null|File $file
     * @return boolean
     */
    public function versionExists(Resource $resource, $version, File $file = null);

}
