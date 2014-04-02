<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use Xi\Filelib\Backend\FindByIdsRequestResolver;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Backend\Finder\Finder;
use ArrayIterator;
use Xi\Filelib\Backend\FindByIdsRequest;

/**
 * Filelib backend platform interface
 */
interface BackendAdapter extends FindByIdsRequestResolver
{
    /**
     * Returns how many times a resource is referenced by files
     *
     * @param  Resource $resource
     * @return int
     */
    public function getNumberOfReferences(Resource $resource);

    /**
     * Finds and returns an array of object ids via finder
     *
     * @param  Finder $finder
     * @return array
     */
    public function findByFinder(Finder $finder);

    /**
     * Finds and returns objects via ids and classnames
     *
     * @param array $ids
     * @param $className
     * @return FindByIdsRequest
     */
    public function findByIds(FindByIdsRequest $request);

    /**
     * Creates a folder
     *
     * @param  Folder           $folder
     * @return Folder           Created folder
     * @throws FilelibException If folder could not be created.
     */
    public function createFolder(Folder $folder);

    /**
     * Deletes a folder
     *
     * @param  Folder           $folder
     * @return boolean          True if deleted successfully.
     * @throws FilelibException If folder could not be deleted.
     */
    public function deleteFolder(Folder $folder);

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @return boolean          True if updated successfully.
     * @throws FilelibException If folder could not be updated.
     */
    public function updateFolder(Folder $folder);

    /**
     * Creates a file
     *
     * @param  File             $file
     * @param  Folder           $folder
     * @return File             Uploaded file
     * @throws FilelibException If file could not be created.
     */
    public function createFile(File $file, Folder $folder);

    /**
     * Deletes a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file);

    /**
     * Updates a file
     *
     * @param  File             $file
     * @return boolean          True if updated successfully.
     * @throws FilelibException If file could not be updated.
     */
    public function updateFile(File $file);

    /**
     * Creates a resource
     *
     * @param  Resource         $resource
     * @return Resource
     * @throws FilelibException If resource could not be created.
     */
    public function createResource(Resource $resource);

    /**
     * Deletes a resource
     *
     * @param  Resource         $resource
     * @return boolean
     * @throws FilelibException If resource could not be deleted.
     */
    public function deleteResource(Resource $resource);

    /**
     * Updates a resource
     *
     * @param  Resource         $resource
     * @return boolean
     * @throws FilelibException If resource could not be updated.
     */
    public function updateResource(Resource $resource);
}
