<?php

namespace Xi\Filelib\Resource;

use PhpCollection\Sequence;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;

interface ResourceRepositoryInterface
{
    /**
     * Updates a resource
     *
     * @param ConcreteResource $resource
     * @return ResourceRepository
     */
    public function update(ConcreteResource $resource);

    /**
     * Finds a resource
     *
     * @param  mixed $id Resource id
     * @return ConcreteResource
     */
    public function find($id);

    /**
     * Finds and returns all resources
     *
     * @return Sequence
     */
    public function findAll();

    /**
     * Deletes a resource
     *
     * @param ConcreteResource $resource
     */
    public function delete(ConcreteResource $resource);

    /**
     * Creates a resource
     *
     * @param ConcreteResource $resource
     * @param string $path
     */
    public function create(ConcreteResource $resource, $path);

    /**
     * @param  File       $file
     * @param  FileUpload $upload
     * @return ConcreteResource
     */
    public function findResourceForUpload(File $file, FileUpload $upload);

    public function findOrCreateResourceForPath($path);
}