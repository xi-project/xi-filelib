<?php

namespace Xi\Filelib\Resource;

use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;

interface ResourceRepositoryInterface
{
    /**
     * Updates a resource
     *
     * @param  Resource         $resource
     * @return ResourceRepository
     */
    public function update(Resource $resource);

    /**
     * Finds a resource
     *
     * @param  mixed $id Resource id
     * @return Resource
     */
    public function find($id);

    /**
     * Finds and returns all resources
     *
     * @return ArrayCollection
     */
    public function findAll();

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     */
    public function delete(Resource $resource);

    /**
     * Creates a resource
     *
     * @param Resource $resource
     * @param string $path
     */
    public function create(Resource $resource, $path);

    /**
     * @param  File       $file
     * @param  FileUpload $upload
     * @return Resource
     */
    public function findResourceForUpload(File $file, FileUpload $upload);
}