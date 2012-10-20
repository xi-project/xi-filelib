<?php

namespace Xi\Filelib\Backend\Platform;

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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var UuidGenerator
     */
    private $uuidGenerator;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

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
     * @param mixed $resource
     * @return array
     */
    protected abstract function exportResource($resource);

    /**
     * @abstract
     * @param $folder
     * @return Folder|false
     */
    protected abstract function exportFolder($folder);

    /**
     * @abstract
     * @param $file
     * @return File|false
     */
    protected abstract function exportFile($file);

    /**
     * Returns whether an identifier is valid for the backend
     *
     * @return boolean
     */
    protected abstract function isValidIdentifier($id);

    /**
     * Finds a folder
     *
     * @param  mixed $id
     * @return Folder|false
     * @throws InvalidArgumentException with invalid folder id
     */
    public function findFolder($id)
    {
        $this->assertValidIdentifier($id, 'Folder');
        $folder = $this->doFindFolder($id);
        if (!$folder) {
            return false;
        }
        return $this->exportFolder($folder);
    }

    /**
     * Finds a resource
     *
     * @param mixed $id
     * @return array|false
     * @throws InvalidArgumentException with invalid folder id
     */
    public function findResource($id)
    {
        $this->assertValidIdentifier($id, 'Resource');

        $resource = $this->doFindResource($id);

        if (!$resource) {
            return false;
        }

        return $this->exportResource($resource);

    }

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
     * Creates a resource
     *
     * @param  Resource         $resource
     * @return Resource         Created folder
     * @throws FilelibException When fails
     */
    public function createResource(Resource $resource)
    {
        return $this->doCreateResource($resource);
    }

    /**
     * Updates a resource
     *
     * @param  Resource $resource
     * @return boolean
     * @throws InvalidArgumentException With invalid id
     */
    public function updateResource(Resource $resource)
    {
        $this->assertValidIdentifier($resource->getId(), 'Resource');
        return (bool) $this->doUpdateResource($resource);
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
     * Returns the number of references to a resource
     *
     * @param Resource $resource
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->doGetNumberOfReferences($resource);
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
        $this->assertValidIdentifier($file->getId(), 'File');
        return (bool) $this->doDeleteFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder                   $folder
     * @return boolean
     * @throws InvalidArgumentException With invalid folder id
     */
    public function updateFolder(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId(), 'Folder');
        return (bool) $this->doUpdateFolder($folder);
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
        $resUpdate = $this->updateResource($file->getResource());
        return (bool) $this->doUpdateFile($file);
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

    /**
     * Generates an UUID
     *
     * @return string
     */
    public function generateUuid()
    {
        return $this->getUuidGenerator()->v4();
    }

    /**
     * Returns event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return UuidGenerator
     */
    protected function getUuidGenerator()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }

        return $this->uuidGenerator;
    }

}
