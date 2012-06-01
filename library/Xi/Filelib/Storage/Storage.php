<?php

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
     * Sets filelib
     *
     * @return FileLibrary
     */
    public function setFilelib(FileLibrary $resourcelib);

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();

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
    public function storeVersion(Resource $resource, $version, $tempResource, File $file = null);

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
    public function retrieveVersion(Resource $resource, $version, File $file = null);

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
    public function deleteVersion(Resource $resource, $version, File $file = null);

}