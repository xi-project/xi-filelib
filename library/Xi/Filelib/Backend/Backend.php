<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Xi\Filelib\FilelibException;

/**
 * Filelib backend interface
 *
 * @package Xi_Filelib
 * @author  pekkis
 */
interface Backend
{
    /**
     * @param mixed $options
     */
    public function __construct($options = array());

    /**
     * Initialization. Is run when backend is set to filelib.
     */
    public function init();

    /**
     * Finds folder
     *
     * @param  mixed        $id
     * @return Folder|false False if folder is not found.
     */
    public function findFolder($id);

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder $folder
     * @return array
     */
    public function findSubFolders(Folder $folder);

    /**
     * Finds all files
     *
     * @return array
     */
    public function findAllFiles();

    /**
     * Finds a file
     *
     * @param  mixed      $id
     * @return File|false False if file is not found.
     */
    public function findFile($id);

    /**
     * Finds files in a folder
     *
     * @param  Folder $folder
     * @return array
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
     * @throws FilelibException If folder could not be deleted.
     */
    public function deleteFolder(Folder $folder);

    /**
     * Deletes a file
     *
     * @param  File $file
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file);

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @throws FilelibException If folder coult not be updated.
     */
    public function updateFolder(Folder $folder);

    /**
     * Updates a file
     *
     * @param  File             $file
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
     * @param  string       $url
     * @return Folder|false False if folder was not found.
     */
    public function findFolderByUrl($url);

    /**
     * Finds file in a folder by filename
     *
     * @param  Folder $folder
     * @param  string $filename
     * @return array
     */
    public function findFileByFilename(Folder $folder, $filename);
}
