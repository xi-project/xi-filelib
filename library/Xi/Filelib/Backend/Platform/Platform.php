<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Backend\Finder\Finder;
use ArrayIterator;

/**
 * Filelib backend platform interface
 */
interface Platform
{
    /**
     * Returns how many times a resource is referenced by files
     *
     * @param Resource $resource
     * @return int
     */
    public function getNumberOfReferences(Resource $resource);

    /**
     * @param ArrayIterator $iter
     * @return ArrayIterator
     */
    public function exportResources(ArrayIterator $iter);

    /**
     * @param ArrayIterator $iter
     * @return ArrayIterator
     */
    public function exportFiles(ArrayIterator $iter);

    /**
     * @param ArrayIterator $iter
     * @return ArrayIterator
     */
    public function exportFolders(ArrayIterator $iter);

    /**
     * @param Finder $finder
     * @return ArrayIterator
     */
    public function findByFinder(Finder $finder);

    public function findResourcesByIds(array $ids = array());

    public function findFilesByIds(array $ids = array());

    public function findFoldersByIds(array $ids = array());


    /**
     * Finds folder
     *
     * @param  mixed       $id
     * @return Folder|false False if folder is not found.
     */
    public function findFolder($id);

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder $folder
     * @return array Array of folders
     */
    public function findSubFolders(Folder $folder);

    /**
     * Finds all files
     *
     * @return array Array of files
     */
    public function findAllFiles();

    /**
     * Finds a file
     *
     * @param  mixed       $id
     * @return File|false False if file is not found.
     */
    public function findFile($id);

    /**
     * Finds files in a folder
     *
     * @param  Folder $folder
     * @return array Array of files
     */
    public function findFilesIn(Folder $folder);

    /**
     * Creates a file
     *
     * @param  File             $file
     * @param  Folder           $folder
     * @return File             Uploaded file
     * @throws FilelibException If file could not be uploaded.
     */
    public function createFile(File $file, Folder $folder);

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
     * Deletes a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file);

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @return boolean          True if updated successfully.
     * @throws FilelibException If folder coult not be updated.
     */
    public function updateFolder(Folder $folder);

    /**
     * Updates a file
     *
     * @param  File             $file
     * @return boolean          True if updated successfully.
     * @throws FilelibException If file could not be updated.
     */
    public function updateFile(File $file);

    /**
     * Returns the root folder. Creates it if it does not exist.
     *
     * @return Folder
     */
    public function findRootFolder();

    /**
     * Finds folder by url
     *
     * @param  string      $url
     * @return Folder|false False if folder was not found.
     */
    public function findFolderByUrl($url);

    /**
     * Finds file in a folder by filename
     *
     * @param  Folder $folder
     * @param  string $filename
     * @return File
     */
    public function findFileByFilename(Folder $folder, $filename);

    /**
     * Finds resource by id
     *
     * @param mixed $id
     * @return Resource
     */
    public function findResource($id);

    /**
     * Finds resources by hash
     *
     * @param string $hash
     * @return array Array of Resources
     */
    public function findResourcesByHash($hash);

    /**
     * Creates a resource
     *
     * @param Resource $resource
     * @return Resource
     */
    public function createResource(Resource $resource);

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function deleteResource(Resource $resource);

    /**
     * Updates a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function updateResource(Resource $resource);


    public function assertValidIdentifier($object);



}
