<?php

namespace Xi\Filelib\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\Resource;
use Xi\Filelib\FilelibException;

/**
 * Filelib Storage interface
 *
 * @author pekkis
 * @todo Something is not perfect yet... Rethink and finalize
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
    public function storeVersion(Resource $resource, $version, $tempResource);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @return ResourceObject
     */
    public function retrieve(Resource $resource);

    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     *
     * @param Resource $resource
     * @param string $version
     * @return ResourceObject
     */
    public function retrieveVersion(Resource $resource, $version);

    /**
     * Deletes a file
     *
     * @param Resource $resource
     */
    public function delete(Resource $resource);

    /**
     * Deletes a version of a file
     *
     * @param Resource $resource
     * @param $version
     */
    public function deleteVersion(Resource $resource, $version);

}