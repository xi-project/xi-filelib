<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\Versionable;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Version;

/**
 * Storage interface
 *
 */
interface StorageAdapter
{
    public function attachTo(FileLibrary $filelib);

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
     * @return Retrieved
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
     * @param Versionable $versionable
     * @param Version $version
     * @param string $tempResource
     * @throws FileIOException
     */
    public function storeVersion(Versionable $versionable, Version $version, $tempResource);

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return Retrieved
     * @throws FileIOException
     */
    public function retrieveVersion(Versionable $versionable, Version $version);

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @throws FileIOException
     */
    public function deleteVersion(Versionable $versionable, Version $version);

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @throws FileIOException
     */
    public function versionExists(Versionable $versionable, Version $version);
}
