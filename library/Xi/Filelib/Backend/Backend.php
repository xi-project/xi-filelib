<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Backend\IdentityMap\IdentityMap;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\ResourceEvent;
use ArrayIterator;
use Xi\Filelib\Events;

class Backend
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var BackendAdapter
     */
    private $platform;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param BackendAdapter                 $platform
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        BackendAdapter $platform
    ) {
        $this->platform = $platform;
        $this->eventDispatcher = $eventDispatcher;

        $this->identityMap = new IdentityMap($this->eventDispatcher);
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     * @return Backend
     */
    public function setCache(Cache $cache)
    {
        $this->eventDispatcher->addSubscriber($cache);
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return BackendAdapter
     */
    public function getBackendAdapter()
    {
        return $this->platform;
    }

    public function getResolvers()
    {
        return ($this->cache) ?
            array(
                $this->identityMap,
                $this->cache,
                $this->platform
            ) :
            array(
                $this->identityMap,
                $this->platform
            );
    }

    /**
     * Finds objects via finder
     *
     * @param  Finder        $finder
     * @return ArrayIterator
     */
    public function findByFinder(Finder $finder)
    {
        $ids = $this->getBackendAdapter()->findByFinder($finder);
        $className = $finder->getResultClass();

        $request = new FindByIdsRequest($ids, $className, $this->eventDispatcher);
        $request->resolve($this->getResolvers());

        $this->identityMap->addMany($request->getResult());
        return $request->getResult();
    }

    /**
     * Finds an object via id and class
     *
     * @param  mixed              $id
     * @param  string             $className
     * @return Identifiable|false
     */
    public function findById($id, $className)
    {
        $request = new FindByIdsRequest($id, $className, $this->eventDispatcher);
        $request->resolve($this->getResolvers());
        $this->identityMap->addMany($request->getResult());
        return $request->getResult()->current();
    }

    /**
     * Creates a file
     *
     * @param  File             $file
     * @param  Folder           $folder
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
            throw new NonUniqueFileException(
                sprintf(
                    'A file with the name "%s" already exists in folder "%s"',
                    $file->getName(),
                    $folder->getName()
                )
            );
        }
        return $this->platform->createFile($file, $folder);
    }

    /**
     * Creates a folder
     *
     * @param  Folder           $folder
     * @throws FilelibException If folder could not be created.
     */
    public function createFolder(Folder $folder)
    {
        if ($folder->getParentId() !== null) {
            if (!$this->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder')) {
                throw new FolderNotFoundException(
                    sprintf('Parent folder was not found with id "%s"', $folder->getParentId())
                );
            }
        }
        return $this->platform->createFolder($folder);
    }

    /**
     * Deletes a file
     *
     * @param  File             $file
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file)
    {
        return $this->platform->deleteFile($file);
    }

    /**
     * Deletes a folder
     *
     * @param  Folder           $folder
     * @throws FilelibException If folder could not be deleted.
     */
    public function deleteFolder(Folder $folder)
    {
        if ($this->findByFinder(new FileFinder(array('folder_id' => $folder->getId())))->count()) {
            throw new FolderNotEmptyException('Can not delete folder with files');
        }
        return $this->platform->deleteFolder($folder);
    }

    /**
     * Updates a file
     *
     * @param  File             $file
     * @throws FilelibException If file could not be updated.
     */
    public function updateFile(File $file)
    {
        if (!$this->findById($file->getFolderId(), 'Xi\Filelib\Folder\Folder')) {
            throw new FolderNotFoundException(
                sprintf('Folder was not found with id "%s"', $file->getFolderId())
            );
        }
        $this->updateResource($file->getResource());
        $this->getBackendAdapter()->updateFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @throws FilelibException If folder could not be updated.
     */
    public function updateFolder(Folder $folder)
    {
        $this->getBackendAdapter()->updateFolder($folder);
    }

    /**
     * Deletes a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be deleted.
     * @todo The event part seems misplaced here.
     */
    public function deleteResource(Resource $resource)
    {
        if ($rno = $this->getNumberOfReferences($resource)) {
            throw new ResourceReferencedException(
                sprintf(
                    'Resource #%s is referenced %s times',
                    $resource->getId(),
                    $rno
                )
            );
        }
        $this->platform->deleteResource($resource);
    }

    /**
     * Creates a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be created.
     */
    public function createResource(Resource $resource)
    {
        return $this->platform->createResource($resource);
    }

    /**
     * Updates a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be updated.
     */
    public function updateResource(Resource $resource)
    {
        $this->getBackendAdapter()->updateResource($resource);
    }

    /**
     * Returns how many times a resource is referenced by files
     *
     * @param  Resource $resource
     * @return int
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->getBackendAdapter()->getNumberOfReferences($resource);
    }
}
