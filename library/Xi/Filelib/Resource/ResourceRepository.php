<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use ArrayIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Profile\ProfileManager;
use DateTime;

/**
 * Resource repository
 *
 * @author pekkis
 *
 */
class ResourceRepository extends AbstractRepository
{
    const COMMAND_CREATE = 'Xi\Filelib\Resource\Command\CreateResourceCommand';
    const COMMAND_UPDATE = 'Xi\Filelib\Resource\Command\UpdateResourceCommand';
    const COMMAND_DELETE = 'Xi\Filelib\Resource\Command\DeleteResourceCommand';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProfileManager
     */
    private $profiles;

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->profiles = $filelib->getProfileManager();
        $this->storage = $filelib->getStorage();
    }

    /**
     * @return array
     */
    public function getCommandDefinitions()
    {
        return array(
            new CommandDefinition(
                self::COMMAND_UPDATE
            ),
            new CommandDefinition(
                self::COMMAND_DELETE
            ),
            new CommandDefinition(
                self::COMMAND_CREATE
            ),
        );
    }

    /**
     * Updates a file
     *
     * @param  Resource         $resource
     * @return ResourceRepository
     */
    public function update(Resource $resource)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_UPDATE, array($resource))
            ->execute();
    }

    /**
     * Finds a file
     *
     * @param  mixed      $id Resource id
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
     * Finds and returns all files
     *
     * @return ArrayIterator
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
        return $this->commander
            ->createExecutable(self::COMMAND_DELETE, array($resource))
            ->execute();
    }

    /**
     * Creates a resource
     *
     * @param Resource $resource
     * @param string $path
     */
    public function create(Resource $resource, $path)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_CREATE, array($resource, $path))
            ->execute();
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
            $resource = new Resource();
            $resource->setDateCreated(new DateTime());
            $resource->setHash($hash);
            $resource->setSize($upload->getSize());
            $resource->setMimetype($upload->getMimeType());
            $resource->setVersions(array());
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
