<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\FileLibrary,
    Xi\Filelib\Configurator,
    Xi\Filelib\FilelibException,
    Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Exception;

/**
 * Abstract backend implementing common methods
 *
 * @author  pekkis
 * @package Xi_Filelib
 */
abstract class AbstractBackend implements Backend
{
    /**
     * @var FileLibrary
     */
    private $filelib;

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
     * @param mixed $options
     */
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * @param  FileLibrary     $filelib
     * @return AbstractBackend
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->filelib = $filelib;

        return $this;
    }

    /**
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    public function init()
    {}

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
     * @throws FilelibException If folder could not be deleted.
     */
    public function deleteFolder(Folder $folder)
    {
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
