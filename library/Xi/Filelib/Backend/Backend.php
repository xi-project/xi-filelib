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
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Exception\FilelibException;
use Xi\Filelib\Exception\FolderNotFoundException;
use Xi\Filelib\Exception\FolderNotEmptyException;
use Xi\Filelib\Exception\ResourceReferencedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Exception\NonUniqueFileException;
use Closure;
use ArrayIterator;

class Backend
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Platform
     */
    private $platform;

    /**
     * @var IdentityMapHelper
     */
    private $identityMapHelper;

    public function __construct(EventDispatcherInterface $eventDispatcher, Platform $platform, IdentityMap $identityMap)
    {
        $this->platform = $platform;
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMapHelper = new IdentityMapHelper($identityMap, $platform);
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
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return IdentityMapHelper
     */
    public function getIdentityMapHelper()
    {
        return $this->identityMapHelper;
    }

    /**
     * Finds objects via finder
     *
     * @param Finder $finder
     * @return ArrayIterator
     */
    public function findByFinder(Finder $finder)
    {
        $resultClass = $finder->getResultClass();
        return $this->getIdentityMapHelper()->tryManyFromIdentityMap(
            $this->getPlatform()->findByFinder($finder),
            $finder->getResultClass(),
            function(Platform $platform, $ids) use ($resultClass) {
                return $platform->findByIds($ids, $resultClass);
            }
        );
    }

    /**
     * Finds an object via id and class
     *
     * @param mixed $id
     * @param string $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return $this->getIdentityMapHelper()->tryOneFromIdentityMap(
            $id,
            $className,
            function(Platform $platform, $id) use ($className) {
                return $platform->findByIds(array($id), $className);
            }
        );
    }

    /**
     * Creates a file
     *
     * @param  File             $file
     * @param  Folder           $folder
     * @return File             Uploaded file
     * @throws FilelibException If file could not be uploaded.
     */
    public function createFile(File $file, Folder $folder)
    {
        $finder = new FileFinder(
            array(
                'folder_id' => $folder->getId(),
                'name' => $file->getName()
            )
        );
        if ($this->findByFinder($finder)->count()) {
            throw new NonUniqueFileException(sprintf(
                'A file with the name "%s" already exists in folder "%s"',
                $file->getName(),
                $folder->getName()
            ));
        }

        return $this->getIdentityMapHelper()->tryAndAddToIdentityMap(function(Platform $platform, File $file, Folder $folder) {
            return $platform->createFile($file, $folder);
        }, $file, $folder);
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
        if (!$this->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder')) {
            throw new FolderNotFoundException(sprintf('Parent folder was not found with id "%s"', $folder->getParentId()));
        }

        return $this->getIdentityMapHelper()->tryAndAddToIdentityMap(function(Platform $platform, Folder $folder) {
            return $platform->createFolder($folder);
        }, $folder);
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
        if ($this->findByFinder(new FileFinder(array('folder_id' => $folder->getId())))->count()) {
            throw new FolderNotEmptyException('Can not delete folder with files');
        }

        return $this->getIdentityMapHelper()->tryAndRemoveFromIdentityMap(function(Platform $platform, Folder $folder) {
            return $platform->deleteFolder($folder);
        }, $folder);
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
        return $this->getIdentityMapHelper()->tryAndRemoveFromIdentityMap(function(Platform $platform, File $file) {
            return $platform->deleteFile($file);
        }, $file);
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
        if (!$this->findById($file->getFolderId(), 'Xi\Filelib\Folder\Folder')) {
            throw new FolderNotFoundException(sprintf('Folder was not found with id "%s"', $file->getFolderId()));
        }
        $this->updateResource($file->getResource());
        return $this->getPlatform()->updateFile($file);
    }

    /**
     * Creates a resource
     *
     * @param Resource $resource
     * @return Resource
     */
    public function createResource(Resource $resource)
    {
        return $this->getIdentityMapHelper()->tryAndAddToIdentityMap(function(Platform $platform, Resource $resource) {
            return $platform->createResource($resource);
        }, $resource);
    }

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function deleteResource(Resource $resource)
    {
        if ($rno = $this->getNumberOfReferences($resource)) {
            throw new ResourceReferencedException("Resource #{$resource->getId()} is referenced {$rno} times and can't be deleted.");
        }

        $ret = $this->getIdentityMapHelper()->tryAndRemoveFromIdentityMap(function(Platform $platform, Resource $resource) {
            return $platform->deleteResource($resource);
        }, $resource);

        $event = new ResourceEvent($resource);
        $this->getEventDispatcher()->dispatch('resource.delete', $event);

        return $ret;

    }

    /**
     * Updates a resource
     *
     * @param Resource $resource
     * @return boolean
     */
    public function updateResource(Resource $resource)
    {
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
