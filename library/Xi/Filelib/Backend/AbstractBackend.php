<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\FilelibException;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Exception\FolderNotFoundException;
use Xi\Filelib\Exception\FolderNotEmptyException;
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
     * Finds folder
     *
     * @param  mixed                    $id
     * @return array|false
     * @throws InvalidArgumentException With invalid folder id
     */
    public function findFolder($id)
    {
        $this->assertValidFolderIdentifier($id);

        $folder = $this->doFindFolder($id);

        if (!$folder) {
            return false;
        }

        return $this->folderToArray($folder);
    }

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder                   $folder
     * @return array
     * @throws InvalidArgumentException With invalid folder id
     */
    public function findSubFolders(Folder $folder)
    {
        $this->assertValidFolderIdentifier($folder->getId());

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
     * @param  mixed                    $id
     * @return array|false
     * @throws InvalidArgumentException With invalid file id
     */
    public function findFile($id)
    {
        $this->assertValidFileIdentifier($id);

        $file = $this->doFindFile($id);

        if (!$file) {
            return false;
        }

        return $this->fileToArray($file);
    }

    /**
     * Finds files in folder
     *
     * @param  Folder                   $folder
     * @return array
     * @throws InvalidArgumentException With invalid folder id
     */
    public function findFilesIn(Folder $folder)
    {
        $this->assertValidFolderIdentifier($folder->getId());

        return array_map(
            array($this, 'fileToArray'),
            $this->doFindFilesIn($folder->getId())
        );
    }

    /**
     * @param  File                    $file
     * @param  Folder                  $folder
     * @return File
     * @throws FolderNotFoundException If folder was not found
     * @throws FilelibException
     */
    public function upload(File $file, Folder $folder)
    {
        if (!$this->findFolder($folder->getId())) {
            throw new FolderNotFoundException(sprintf(
                'Folder was not found with id "%s"',
                $folder->getId()
            ));
        }

        try {
            return $this->doUpload($file, $folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Creates a folder
     *
     * @param  Folder                  $folder
     * @return Folder                  Created folder
     * @throws FolderNotFoundException If parent folder was not found
     */
    public function createFolder(Folder $folder)
    {
        if (!$this->findFolder($folder->getParentId())) {
            throw new FolderNotFoundException(sprintf(
                'Parent folder was not found with id "%s"',
                $folder->getParentId()
            ));
        }

        return $this->doCreateFolder($folder);
    }

    /**
     * Deletes a folder
     *
     * @param  Folder                  $folder
     * @return boolean                 True if deleted successfully
     * @throws FolderNotEmptyException If folder contains files
     */
    public function deleteFolder(Folder $folder)
    {
        if (count($this->findFilesIn($folder))) {
            throw new FolderNotEmptyException('Can not delete folder with files');
        }

        return (bool) $this->doDeleteFolder($folder);
    }

    /**
     * Deletes a file
     *
     * @param  File                     $file
     * @return boolean
     * @throws InvalidArgumentException With invalid file id
     */
    public function deleteFile(File $file)
    {
        $this->assertValidFileIdentifier($file->getId());

        return (bool) $this->doDeleteFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder                   $folder
     * @return boolean
     * @throws FilelibException
     * @throws InvalidArgumentException With invalid folder id
     * @throws FilelibException
     */
    public function updateFolder(Folder $folder)
    {
        $this->assertValidFolderIdentifier($folder->getId());

        try {
            return (bool) $this->doUpdateFolder($folder);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Updates a file
     *
     * @param  File                    $file
     * @return boolean
     * @throws FolderNotFoundException If folder was not found
     */
    public function updateFile(File $file)
    {
        if (!$this->findFolder($file->getFolderId())) {
            throw new FolderNotFoundException(sprintf(
                'Folder was not found with id "%s"',
                $file->getFolderId()
            ));
        }

        return (bool) $this->doUpdateFile($file);
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
     * @param  string                   $url
     * @return array|false
     * @throws InvalidArgumentException With invalid folder URL
     */
    public function findFolderByUrl($url)
    {
        $this->assertValidFolderUrl($url);

        $folder = $this->doFindFolderByUrl($url);

        if (!$folder) {
            return false;
        }

        return $this->folderToArray($folder);
    }

    /**
     * @param  Folder                   $folder
     * @param  string                   $filename
     * @return array
     * @throws InvalidArgumentException With invalid folder id
     */
    public function findFileByFilename(Folder $folder, $filename)
    {
        $this->assertValidFolderIdentifier($folder->getId());

        $file = $this->doFindFileByFilename($folder, $filename);

        if (!$file) {
            return false;
        }

        return $this->fileToArray($file);
    }

    /**
     * @param  string                   $url
     * @throws InvalidArgumentException
     */
    protected function assertValidFolderUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(sprintf(
                'Folder URL must be a string, %s given',
                gettype($url)
            ));
        }
    }

    /**
     * @param  mixed                    $id
     * @throws InvalidArgumentException
     */
    protected function assertValidFolderIdentifier($id)
    {
        if (!is_int($id)) {
            $this->throwInvalidArgumentException(
                $id,
                'Folder id must be an integer, %s (%s) given'
            );
        }
    }

    /**
     * @param  mixed                    $id
     * @throws InvalidArgumentException
     */
    protected function assertValidFileIdentifier($id)
    {
        if (!is_int($id)) {
            $this->throwInvalidArgumentException(
                $id,
                'File id must be an integer, %s (%s) given'
            );
        }
    }

    /**
     * @param  mixed                    $id
     * @param  string                   $message
     * @throws InvalidArgumentException
     */
    protected function throwInvalidArgumentException($id, $message)
    {
        throw new InvalidArgumentException(sprintf(
            $message,
            gettype($id),
            $id
        ));
    }
}
