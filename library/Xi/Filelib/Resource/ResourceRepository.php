<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
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
    public function update(Resource $resource)
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
     * @return Resource
     */
    public function find($id)
    {
        $resource = $this->backend->findById($id, 'Xi\Filelib\Resource\Resource');

        if (!$resource) {
            return false;
        }

        return $resource;
    }

    /**
     * Finds and returns all resources
     *
     * @return ArrayCollection
     */
    public function findAll()
    {
        $resources = $this->backend->findByFinder(new ResourceFinder());
        return $resources;
    }

    /**
     * Deletes a resource
     *
     * @param Resource $resource
     */
    public function delete(Resource $resource)
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
     * @param Resource $resource
     * @param string $path
     */
    public function create(Resource $resource, $path)
    {
        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_CREATE, $event);

        $this->backend->createResource($resource);
        $this->storage->store($resource, $path);

        $event = new ResourceEvent($resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_CREATE, $event);

        return $resource;
    }

    /**
     * @param  File       $file
     * @param  FileUpload $upload
     * @return Resource
     */
    public function findResourceForUpload(File $file, FileUpload $upload)
    {
        $file = clone $file;

        $hash = sha1_file($upload->getRealPath());
        $profileObj = $this->profiles->getProfile($file->getProfile());

        $finder = new ResourceFinder(array('hash' => $hash));
        $resources = $this->backend->findByFinder($finder);

        if ($resources) {
            foreach ($resources as $resource) {
                if (!$resource->isExclusive()) {
                    $file->setResource($resource);

                    if (!$profileObj->isSharedResourceAllowed($file)) {
                        $file->unsetResource();
                    }
                    break;
                }
            }
        }

        if (!$file->getResource()) {
            $resource = Resource::create();
            $resource->setDateCreated(new DateTime());
            $resource->setHash($hash);
            $resource->setSize($upload->getSize());
            $resource->setMimetype($upload->getMimeType());
            $file->setResource($resource);
            if (!$profileObj->isSharedResourceAllowed($file)) {
                $resource->setExclusive(true);
            }

            $this->create($resource, $upload->getRealPath());
            $file->setResource($resource);
        }

        return $file->getResource();
    }
}
