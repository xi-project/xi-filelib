<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\ResourceFinder;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Tool\UuidGenerator\UuidGenerator;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Exception\FolderNotFoundException;
use Xi\Filelib\Exception\FolderNotEmptyException;
use Xi\Filelib\Exception\NonUniqueFileException;
use Xi\Filelib\Exception\ResourceReferencedException;
use Xi\Filelib\Event\ResourceEvent;
use Exception;

/**
 * Abstract backend implementing common methods
 *
 * @author pekkis <pekkisx@gmail.com>
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
abstract class AbstractPlatform implements Platform
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
     * @param  Resource $resource
     * @return boolean
     */
    protected abstract function doUpdateResource(Resource $resource);

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
     * @param $id
     * @return Resource|false
     */
    protected abstract function doFindResource($id);

    /**
     * @param $hash
     * @return array
     */
    protected abstract function doFindResourcesByHash($hash);

    /**
     * @param Resource $resource
     * @return Resource
     */
    protected abstract function doCreateResource(Resource $resource);

    /**
     * @param Resource $resource
     * @return boolean
     */
    protected abstract function doDeleteResource(Resource $resource);

    /**
     * Returns the number of references for a resource
     *
     * @param Resource $resource
     * @return integer
     */
    protected abstract function doGetNumberOfReferences(Resource $resource);

    /**
     * Returns whether an identifier is valid for the backend
     *
     * @return boolean
     */
    protected abstract function isValidIdentifier($id);

    /**
     * Finds resources by hash
     *
     * @param string $hash
     * @return array
     */
    public function findResourcesByHash($hash)
    {
        return array_map(
            array($this, 'exportResource'),
            $this->doFindResourcesByHash($hash)
        );
    }

    /**
     * Deletes a resource
     *
     * @param  Resource         $resource
     * @return boolean          True if deleted successfully.
     * @throws ResourceReferencedException If resource has references
     */
    public function deleteResource(Resource $resource)
    {
        if ($rno = $this->getNumberOfReferences($resource)) {
            throw new ResourceReferencedException("Resource #{$resource->getId()} is referenced {$rno} times and can't be deleted.");
        }

        $ret = (bool) $this->doDeleteResource($resource);

        if ($ret) {
            $event = new ResourceEvent($resource);
            $this->getEventDispatcher()->dispatch('resource.delete', $event);
        }

        return $ret;
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
        $this->assertValidIdentifier($folder->getId(), 'Folder');

        return array_map(
            array($this, 'exportFolder'),
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
            array($this, 'exportFile'),
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
        $this->assertValidIdentifier($id, 'File');

        $file = $this->doFindFile($id);

        if (!$file) {
            return false;
        }

        return $this->exportFile($file);
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
        $this->assertValidIdentifier($folder->getId(), 'Folder');

        return array_map(
            array($this, 'exportFile'),
            $this->doFindFilesIn($folder->getId())
        );
    }

    /**
     * @param  File                    $file
     * @param  Folder                  $folder
     * @return File
     * @throws FolderNotFoundException If folder was not found
     * @throws NonUniqueFileException  If file already exists folder
     */
    public function upload(File $file, Folder $folder)
    {
        if (!$this->findFolder($folder->getId())) {
            throw new FolderNotFoundException(sprintf(
                'Folder was not found with id "%s"',
                $folder->getId()
            ));
        }

        return $this->doUpload($file, $folder);
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
    }

    /**
     * Finds the root folder
     *
     * @return array
     */
    public function findRootFolder()
    {
        return $this->exportFolder($this->doFindRootFolder());
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

        return $this->exportFolder($folder);
    }

    /**
     * @param  Folder                   $folder
     * @param  string                   $filename
     * @return array
     * @throws InvalidArgumentException With invalid folder id
     */
    public function findFileByFilename(Folder $folder, $filename)
    {
        $this->assertValidIdentifier($folder->getId(), 'Folder');

        $file = $this->doFindFileByFilename($folder, $filename);

        if (!$file) {
            return false;
        }

        return $this->exportFile($file);
    }

    /**
     * Asserts that an identifier is valid
     *
     * @param  $id Identifier
     * @param  $exceptionObjectName Object name (Folder, File, Resource)
     * @throws InvalidArgumentException
     */
    public function assertValidIdentifier($id, $exceptionObjectName)
    {
        $isValid = $this->isValidIdentifier($id);

        if (!$isValid) {
            throw $this->createInvalidArgumentException(
                $id,
                "{$exceptionObjectName} id '%s' is invalid"
            );
        }
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
     * @param  string                   $message
     * @return InvalidArgumentException
     */
    protected function createInvalidArgumentException($id, $message)
    {
        return new InvalidArgumentException(sprintf(
            $message,
            $id
        ));
    }

    /**
     * @param  File                   $file
     * @param  Folder                 $folder
     * @throws NonUniqueFileException
     *
     * @internal Should be protected but can't because of PHP 5.3 closure scope
     */
    public function throwNonUniqueFileException(File $file, Folder $folder)
    {
        throw new NonUniqueFileException(sprintf(
            'A file with the name "%s" already exists in folder "%s"',
            $file->getName(),
            $folder->getName()
        ));
    }

    public function findByFinder(Finder $finder)
    {
        switch(get_class($finder)) {

            case 'Xi\Filelib\Backend\Finder\FileFinder':
                return $this->findFilesByFinder($finder);

            case 'Xi\Filelib\Backend\Finder\FolderFinder':
                return $this->findFoldersByFinder($finder);

            case 'Xi\Filelib\Backend\Finder\ResourceFinder':
                return $this->findResourcesByFinder($finder);
        }

    }

    protected abstract function findResourcesByFinder(ResourceFinder $finder);

    protected abstract function findFoldersByFinder(FolderFinder $finder);

    protected abstract function findFilesByFinder(FileFinder $finder);


}
