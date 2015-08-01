<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use DateTime;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpCollection\Sequence;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Storage\Storage;

/**
 * Resource repository
 *
 * @author pekkis
 *
 */
class ResourceRepository extends AbstractRepository implements ResourceRepositoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProfileManager
     */
    private $profiles;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->profiles = $filelib->getProfileManager();
        $this->storage = $filelib->getStorage();
    }

    /**
     * Updates a resource
     *
     * @param  Resource         $resource
     * @return ResourceRepository
     */
    public function update(ConcreteResource $resource)
    {
        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_UPDATE, $event);

        $this->backend->updateResource($resource);

        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_UPDATE, $event);

        return $resource;
    }

    /**
     * Finds a resource
     *
     * @param  mixed $id Resource id
     * @return ConcreteResource
     */
    public function find($id)
    {
        $resource = $this->backend->findById($id, 'Xi\Filelib\Resource\ConcreteResource');

        if (!$resource) {
            return false;
        }

        return $resource;
    }

    /**
     * Finds and returns all resources
     *
     * @return Sequence
     */
    public function findAll()
    {
        $resources = $this->backend->findByFinder(new ResourceFinder());
        return $resources;
    }

    /**
     * Deletes a resource
     *
     * @param ConcreteResource $resource
     */
    public function delete(ConcreteResource $resource)
    {
        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_DELETE, $event);

        $this->backend->deleteResource($resource);
        $this->storage->delete($resource);

        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_DELETE, $event);

        return $resource;
    }

    /**
     * Creates a resource
     *
     * @param ConcreteResource $resource
     * @param string $path
     */
    public function create(ConcreteResource $resource, $path)
    {
        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_CREATE, $event);
        $resource->setUuid(Uuid::uuid4()->toString());
        $this->backend->createResource($resource);
        $this->storage->store($resource, $path);

        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_CREATE, $event);

        return $resource;
    }

    public function findOrCreateResourceForPath($path)
    {
        if (!$path instanceof FileObject) {
            $path = new FileObject($path);
        }

        $hash = sha1_file($path->getRealPath());
        $finder = new ResourceFinder(array('hash' => $hash));
        $resources = $this->backend->findByFinder($finder);

        if ($resources->count()) {
            return $resources->first();
        }

        $resource = ConcreteResource::create();
        $resource->setDateCreated(new DateTime());
        $resource->setHash($hash);
        $resource->setSize($path->getSize());
        $resource->setMimetype($path->getMimeType());

        $this->create($resource, $path->getRealPath());

        return $resource;
    }



    /**
     * @param  File       $file
     * @param  FileUpload $upload
     * @return ConcreteResource
     */
    public function findResourceForUpload(File $file, FileUpload $upload)
    {
        return $this->findOrCreateResourceForPath($upload->getFileObject());
    }
}
