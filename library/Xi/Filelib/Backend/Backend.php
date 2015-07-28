<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Xi\Filelib\Backend\Cache\Adapter\NullCacheAdapter;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\IdentityMap\IdentityMap;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Tool\LazyReferenceResolver;

class Backend
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LazyReferenceResolver
     */
    private $adapter;

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
     * @param BackendAdapter $adapter
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $adapter
    ) {
        $this->adapter = new LazyReferenceResolver($adapter, 'Xi\Filelib\Backend\Adapter\BackendAdapter');
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = new IdentityMap($this->eventDispatcher);
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        // NullCacheAdapter just makes sure that theres no need for cache-enabled/disabled checks in client code
        if (!$this->cache) {
            $this->cache = new Cache(new NullCacheAdapter());
        }

        return $this->cache;
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
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
     * @return LazyReferenceResolver
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getResolvers()
    {
        return ($this->cache) ?
            array(
                $this->identityMap,
                $this->cache,
                $this->resolveAdapter()
            ) :
            array(
                $this->identityMap,
                $this->resolveAdapter()
            );
    }

    /**
     * Finds objects via finder
     *
     * @param  Finder        $finder
     * @return ArrayCollection
     */
    public function findByFinder(Finder $finder)
    {
        $ids = $this->resolveAdapter()->findByFinder($finder);
        $className = $finder->getResultClass();

        $request = new FindByIdsRequest($ids, $className, $this->eventDispatcher);
        $request->resolve($this->getResolvers());

        $this->identityMap->addMany($request->getResult());
        return $request->getResult();
    }

    /**
     * @param mixed $id
     * @param string $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return $this->findByIds(array($id), $className)->first();
    }

    /**
     * Finds objects via ids and class
     *
     * @param array $ids
     * @param string $className
     * @return ArrayCollection
     */
    public function findByIds(array $ids, $className)
    {
        $request = new FindByIdsRequest($ids, $className, $this->eventDispatcher);
        $request->resolve($this->getResolvers());
        $this->identityMap->addMany($request->getResult());
        return $request->getResult();
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
        return $this->resolveAdapter()->createFile($file, $folder);
    }

    /**
     * Creates a folder
     *
     * @param  Folder $folder
     * @return Folder
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
        return $this->resolveAdapter()->createFolder($folder);
    }

    /**
     * Deletes a file
     *
     * @param  File             $file
     * @throws FilelibException If file could not be deleted.
     */
    public function deleteFile(File $file)
    {
        return $this->resolveAdapter()->deleteFile($file);
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
        return $this->resolveAdapter()->deleteFolder($folder);
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
        $this->resolveAdapter()->updateFile($file);
    }

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @throws FilelibException If folder could not be updated.
     */
    public function updateFolder(Folder $folder)
    {
        $this->resolveAdapter()->updateFolder($folder);
    }

    /**
     * Deletes a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be deleted.
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
        $this->resolveAdapter()->deleteResource($resource);
    }

    /**
     * Creates a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be created.
     */
    public function createResource(Resource $resource)
    {
        return $this->resolveAdapter()->createResource($resource);
    }

    /**
     * Updates a resource
     *
     * @param  Resource         $resource
     * @throws FilelibException If resource could not be updated.
     */
    public function updateResource(Resource $resource)
    {
        $this->resolveAdapter()->updateResource($resource);
    }

    /**
     * Returns how many times a resource is referenced by files
     *
     * @param  Resource $resource
     * @return int
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->resolveAdapter()->getNumberOfReferences($resource);
    }

    /**
     * @return BackendAdapter
     */
    private function resolveAdapter()
    {
        return $this->adapter->resolve();
    }
}
