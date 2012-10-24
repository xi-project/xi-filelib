<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Tool\UuidGenerator\UuidGenerator;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Xi\Filelib\Exception\FolderNotFoundException;
use Xi\Filelib\Exception\FolderNotEmptyException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Backend
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var UuidGenerator
     */
    private $uuidGenerator;

    /**
     * @var Platform
     */
    private $platform;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    public function __construct(EventDispatcherInterface $eventDispatcher, Platform $platform, IdentityMap $identityMap)
    {
        $this->platform = $platform;
        $this->identityMap = $identityMap;
        $this->eventDispatcher = $eventDispatcher;
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
    public function getUuidGenerator()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }
        return $this->uuidGenerator;
    }



    /**
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Finds folder
     *
     * @param  mixed       $id
     * @return Folder|false False if folder is not found.
     */
    public function findFolder($id)
    {
        $ret = $this->getPlatform()->findByFinder(new FolderFinder(array('id' => $id)));
        return $ret->current();
    }

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder $folder
     * @return array Array of folders
     */
    public function findSubFolders(Folder $folder)
    {
        $ret = $this->getPlatform()->findByFinder(new FolderFinder(array('parent_id' => $folder->g)));
        return $this->getPlatform()->exportFolders($ret);
    }

    /**
     * Finds all files
     *
     * @return array Array of files
     */
    public function findAllFiles()
    {
        $ret = $this->getPlatform()->findByFinder(new FileFinder(array()));
        return $this->getPlatform()->exportFiles($ret);
    }

    /**
     * Finds a file
     *
     * @param  mixed       $id
     * @return File|false False if file is not found.
     */
    public function findFile($id)
    {
        $ret = $this->getPlatform()->findByFinder(new FileFinder(array('id' => $id)));
        return $this->getPlatform()->exportFiles($ret)->current();
    }

    /**
     * Finds files in a folder
     *
     * @param  Folder $folder
     * @return array Array of files
     */
    public function findFilesIn(Folder $folder)
    {
        $ret = $this->getPlatform()->findByFinder(new FileFinder(array('folder_id' => $folder->getId())));
        return $this->getPlatform()->exportFile($ret)->current();
    }

    /**
     * Uploads a file
     *
     * @param  File             $file
     * @param  Folder           $folder
     * @return File             Uploaded file
     * @throws FilelibException If file could not be uploaded.
     */
    public function upload(File $file, Folder $folder)
    {
        return $this->getPlatform()->upload($file, $folder);
    }

    /**
     * Creates a folder
     *
     * @param  Folder           $folder
     * @return Folder           Created folder
     * @throws FilelibException If folder could not be created.
     */
    public function createFolder(Folder $folder)
    {
        return $this->getPlatform()->createFolder($folder);
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
        return $this->getPlatform()->deleteFolder($folder);
    }

    /**
     * Deletes a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file)
    {
        return $this->getPlatform()->deleteFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @return boolean          True if updated successfully.
     * @throws FilelibException If folder coult not be updated.
     */
    public function updateFolder(Folder $folder)
    {
        $this->getPlatform()->assertValidIdentifier($folder);
        return $this->getPlatform()->updateFolder($folder);
    }

    /**
     * Updates a file
     *
     * @param  File             $file
     * @return boolean          True if updated successfully.
     * @throws FilelibException If file could not be updated.
     */
    public function updateFile(File $file)
    {
        $this->getPlatform()->assertValidIdentifier($file);
        if (!$this->findFolder($file->getFolderId())) {
            throw new FolderNotFoundException(sprintf('Folder was not found with id "%s"', $file->getFolderId()));
        }
        $this->updateResource($file->getResource());
        return $this->getPlatform()->updateFile($file);
    }

    /**
     * Returns the root folder. Creates it if it does not exist.
     *
     * @return Folder
     */
    public function findRootFolder()
    {
        return $this->getPlatform()->findRootFolder();
    }

    /**
     * Finds folder by url
     *
     * @param  string      $url
     * @return Folder|false False if folder was not found.
     */
    public function findFolderByUrl($url)
    {
        return $this->getPlatform()->findFolderByUrl($url);
    }

    /**
     * Finds file in a folder by filename
     *
     * @param  Folder $folder
     * @param  string $filename
     * @return File
     */
    public function findFileByFilename(Folder $folder, $filename)
    {
        return $this->getPlatform()->findFileByFilename($folder, $filename);
    }

    /**
     * Finds resource by id
     *
     * @param mixed $id
     * @return Resource
     */
    public function findResource($id)
    {
        if ($ret = $this->getIdentityMap()->get($id, 'Xi\Filelib\File\Resource')) {
            return $ret;
        }

        $ret = $this->getPlatform()->findResourcesByIds(array($id));

        if (!$ret->count()) {
            return false;
        }

        $this->getIdentityMap()->add($ret->current());
        return $ret->current();
    }

    /**
     * Finds resources by hash
     *
     * @param string $hash
     * @return array Array of Resources
     */
    public function findResourcesByHash($hash)
    {
        return false;
    }

    /**
     * Creates a resource
     *
     * @param Resource $resource
     * @return Resource
     */
    public function createResource(Resource $resource)
    {
        return $this->getPlatform()->createResource($resource);
    }

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function deleteResource(Resource $resource)
    {
        return $this->getPlatform()->deleteResource($resource);
    }

    /**
     * Updates a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function updateResource(Resource $resource)
    {
        $this->getPlatform()->assertValidIdentifier($resource);
        return $this->getPlatform()->updateResource($resource);
    }


    /**
     * Returns how many times a resource is referenced by files
     *
     * @param Resource $resource
     * @return int
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->getPlatform()->getNumberOfReferences($resource);
    }


}
