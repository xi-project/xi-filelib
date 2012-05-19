<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\FilelibException;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Exception;

/**
 * Abstract backend implementing common methods
 *
 * @author  pekkis
 * @package Xi_Filelib
 */
abstract class AbstractBackend implements Backend
{
    /**
     * @param  mixed      $id
     * @return array|null
     */
    protected abstract function doFindFolder($id);

    /**
     * @param  mixed $id
     * @return array
     */
    protected abstract function doFindSubFolders($id);

    /**
     * @return array
     */
    protected abstract function doFindAllFiles();

    /**
     * @param  mixed      $id
     * @return array|null
     */
    protected abstract function doFindFile($id);

    /**
     * @param  mixed $id
     * @return array
     */
    protected abstract function doFindFilesIn($id);

    /**
     * @param  File   $file
     * @param  Folder $folder
     * @return File
     */
    protected abstract function doUpload(File $file, Folder $folder);

    /**
     * @param  Folder $folder
     * @return Folder
     */
    protected abstract function doCreateFolder(Folder $folder);

    /**
     * @param  Folder  $folder
     * @return boolean
     */
    protected abstract function doDeleteFolder(Folder $folder);

    /**
     * @param  File    $file
     * @return boolean
     */
    protected abstract function doDeleteFile(File $file);

    /**
     * @param  Folder  $folder
     * @return boolean
     */
    protected abstract function doUpdateFolder(Folder $folder);

    /**
     * @param  File    $file
     * @return boolean
     */
    protected abstract function doUpdateFile(File $file);

    /**
     * @return array
     */
    protected abstract function doFindRootFolder();

    /**
     * @param  string     $url
     * @return array|null
     */
    protected abstract function doFindFolderByUrl($url);

    /**
     * @param  Folder     $folder
     * @param  string     $filename
     * @return array|null
     */
    protected abstract function doFindFileByFilename(Folder $folder, $filename);

    protected abstract function doFindResource($id);

    protected abstract function doFindResourcesByHash($hash);

    protected abstract function doCreateResource(Resource $resource);

    protected abstract function doDeleteResource(Resource $resource);

    /**
     * @param  mixed $folder
     * @return array
     */
    protected abstract function folderToArray($folder);

    /**
     * @param  mixed $file
     * @return array
     */
    protected abstract function fileToArray($file);

    /**
     * @param mixed $resource
     * @return array
     */
    protected abstract function resourceToArray($resource);

    /**
     * Finds folder
     *
     * @param  mixed       $id
     * @return array|false
     */
    public function findFolder($id)
    {
        $this->assertValidIdentifier($id);

        $folder = $this->doFindFolder($id);

        if (!$folder) {
            return false;
        }

        return $this->folderToArray($folder);
    }


    public function findResource($id)
    {
        $this->assertValidIdentifier($id);

        $resource = $this->doFindResource($id);

        if (!$resource) {
            return false;
        }

        return $this->resourceToArray($folder);

    }

    public function findResourcesByHash($hash)
    {
        return array_map(
            array($this, 'resourceToArray'),
            $this->doFindResourcesByHash($hash)
        );
    }


    /**
     * Creates a resource
     *
     * @param  Resource         $resource
     * @return Resource         Created folder
     * @throws FilelibException When fails
     */
    public function createResource(Resource $resource)
    {
        try {
            return $this->doCreateResource($resource);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a resource
     *
     * @param  Resource         $resource
     * @return boolean          True if deleted successfully.
     * @throws FilelibException If folder could not be deleted or if folder
     *                          contains files.
     */
    public function deleteResource(Resource $resource)
    {
        try {
            return (bool) $this->doDeleteResource($resource);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder $folder
     * @return array
     */
    public function findSubFolders(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

        return array_map(
            array($this, 'folderToArray'),
            $this->doFindSubFolders($folder->getId())
        );
    }

    /**
     * Finds all files
     *
     * @return array
     */
    public function findAllFiles()
    {
        return array_map(
            array($this, 'fileToArray'),
            $this->doFindAllFiles()
        );
    }

    /**
     * Finds a file
     *
     * @param  mixed       $id
     * @return array|false
     */
    public function findFile($id)
    {
        $this->assertValidIdentifier($id);

        $file = $this->doFindFile($id);

        if (!$file) {
            return false;
        }

        return $this->fileToArray($file);
    }

    /**
     * Finds files in folder
     *
     * @param  Folder $folder
     * @return array
     */
    public function findFilesIn(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

        return array_map(
            array($this, 'fileToArray'),
            $this->doFindFilesIn($folder->getId())
        );
    }

    /**
     * @param  File             $file
     * @param  Folder           $folder
     * @return File
     * @throws FilelibException
     */
    public function upload(File $file, Folder $folder)
    {
        try {
            return $this->doUpload($file, $folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Creates a folder
     *
     * @param  Folder           $folder
     * @return Folder           Created folder
     * @throws FilelibException When fails
     */
    public function createFolder(Folder $folder)
    {
        try {
            return $this->doCreateFolder($folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a folder
     *
     * @param  Folder           $folder
     * @return boolean          True if deleted successfully.
     * @throws FilelibException If folder could not be deleted or if folder
     *                          contains files.
     */
    public function deleteFolder(Folder $folder)
    {
        if (count($this->findFilesIn($folder))) {
            throw new FilelibException('Can not delete folder with files');
        }

        try {
            return (bool) $this->doDeleteFolder($folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a file
     *
     * @param  File    $file
     * @return boolean
     */
    public function deleteFile(File $file)
    {
        $this->assertValidIdentifier($file->getId());

        return (bool) $this->doDeleteFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @return boolean
     * @throws FilelibException When fails
     */
    public function updateFolder(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

        try {
            return (bool) $this->doUpdateFolder($folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Updates a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException When fails
     */
    public function updateFile(File $file)
    {
        try {
            return (bool) $this->doUpdateFile($file);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Finds the root folder
     *
     * @return array
     */
    public function findRootFolder()
    {
        return $this->folderToArray($this->doFindRootFolder());
    }

    /**
     * Finds folder by url
     *
     * @param  string      $url
     * @return array|false
     */
    public function findFolderByUrl($url)
    {
        $this->assertValidUrl($url);

        $folder = $this->doFindFolderByUrl($url);

        if (!$folder) {
            return false;
        }

        return $this->folderToArray($folder);
    }

    /**
     * @param  Folder $folder
     * @param  string $filename
     * @return array
     */
    public function findFileByFilename(Folder $folder, $filename)
    {
        $this->assertValidIdentifier($folder->getId());

        $file = $this->doFindFileByFilename($folder, $filename);

        if (!$file) {
            return false;
        }

        return $this->fileToArray($file);
    }

    /**
     * @param  string           $url
     * @throws FilelibException
     */
    protected function assertValidUrl($url)
    {
        if (is_array($url) || is_object($url)) {
            throw new FilelibException('URL must be a string.');
        }
    }

    /**
     * @param  mixed            $id
     * @throws FilelibException
     */
    protected function assertValidIdentifier($id)
    {
        if (!is_numeric($id)) {
            throw new FilelibException(sprintf(
                'Id must be numeric; %s given.',
                $id
            ));
        }
    }
}
