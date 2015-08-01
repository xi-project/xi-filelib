<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Storage\Retrieved;

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
    public function store(ConcreteResource $resource, $tempResource);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param ConcreteResource $resource
     * @return Retrieved
     * @throws FilelibException
     */
    public function retrieve(ConcreteResource $resource);

    /**
     * Returns whether stored file exists
     *
     * @param  ConcreteResource $resource
     * @return boolean
     */
    public function exists(ConcreteResource $resource);

    /**
     * Deletes a file
     *
     * @param  Resource         $resource
     * @return boolean
     * @throws FilelibException
     */
    public function delete(ConcreteResource $resource);
}
