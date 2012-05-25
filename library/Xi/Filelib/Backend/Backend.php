<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\FilelibException;

/**
 * Filelib backend interface
 *
 * @package Xi_Filelib
 * @author  pekkis
 */
interface Backend
{
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
     * Uploads a file
     *
     * @param  File             $file
     * @param  Folder           $folder
     * @return File             Uploaded file
     * @throws FilelibException If file could not be uploaded.
     */
    public function upload(File $file, Folder $folder);

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
     */
    public function findResource($id);

    /**
     * Finds resources by hash
     *
     * @param string $hash
     */
    public function findResourcesByHash($hash);

    /**
     * Creates a resource
     *
     * @param Resource $resource
     */
    public function createResource(Resource $resource);

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     */
    public function deleteResource(Resource $resource);


    /**
     * Updates a resource
     *
     * @param Resource $resource
     */
    public function updateResource(Resource $resource);


    /**
     * Returns how many times a resource is referenced by files
     *
     * @oaram Resource $resource
     */
    public function getNumberOfReferences(Resource $resource);


    /**
     * Returns whether an identifier is valid for the backend
     *
     * @return boolean
     */
    public function isValidIdentifier($id);

    /**
     * @return string
     */
    public function generateUuid();

}
