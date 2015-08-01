<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder;

use Rhumsaa\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PhpCollection\Sequence;
use Xi\Filelib\AbstractRepository;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\LogicException;

class FolderRepository extends AbstractRepository implements FolderRepositoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->fileRepository = $filelib->getFileRepository();
    }

    /**
     * Returns directory route for folder
     *
     * @param  Folder $folder
     * @return string
     */
    private function buildRoute(Folder $folder)
    {
        $rarr = array();

        array_unshift($rarr, $folder->getName());
        $imposter = clone $folder;
        while ($imposter = $this->findParentFolder($imposter)) {

            if ($imposter->getParentId()) {
                array_unshift($rarr, $imposter->getName());
            }
        }

        return implode('/', $rarr);
    }

    /**
     * Creates a folder
     *
     * @param Folder $folder
     */
    public function create(Folder $folder)
    {
        if ($folder->getParentId() === null && $folder->getName() !== 'root') {
            throw new LogicException('Only one root folder may exist');
        }

        if ($folder->getParentId()) {
            $parentFolder = $this->find($folder->getParentId());
            $event = new FolderEvent($parentFolder);
            $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);
        }

        $route = $this->buildRoute($folder);
        $folder->setUrl($route);
        $folder->setUuid(Uuid::uuid4()->toString());

        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_CREATE, $event);

        $this->backend->createFolder($folder);

        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_AFTER_CREATE, $event);

        return $folder;
    }

    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     */
    public function delete(Folder $folder)
    {
        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_DELETE, $event);

        foreach ($this->findSubFolders($folder) as $childFolder) {
            $this->delete($childFolder);
        }

        foreach ($this->findFiles($folder) as $file) {
            $this->fileRepository->delete($file);
        }

        $this->backend->deleteFolder($folder);

        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(
            Events::FOLDER_AFTER_DELETE,
            $event
        );

        return true;
    }

    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder)
    {
        $route = $this->buildRoute($folder);
        $folder->setUrl($route);

        $this->backend->updateFolder($folder);

        foreach ($this->findFiles($folder) as $file) {
            $this->fileRepository->update($file);
        }

        foreach ($this->findSubFolders($folder) as $subFolder) {
            $this->update($subFolder);
        }

        $event = new FolderEvent($folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_AFTER_UPDATE, $event);

        return $folder;
    }

    /**
     * Finds the root folder
     *
     * @return Folder
     */
    public function findRoot()
    {
        $folder = $this->backend->findByFinder(
            new FolderFinder(array('parent_id' => null))
        )->first()->getOrElse(null);

        if (!$folder) {
            $folder = $this->createRoot();
        }
        return $folder;
    }

    /**
     * Creates root folder. Should only be called once in the history of a filebanksta app.
     */
    private function createRoot()
    {
        $folder = Folder::create();
        $folder->setName('root');
        $this->create($folder);

        return $folder;
    }

    /**
     * Finds a folder
     *
     * @param  mixed  $id Folder id
     * @return Folder
     */
    public function find($id)
    {
        $folder = $this->backend->findById($id, 'Xi\Filelib\Folder\Folder');
        return $folder;
    }

    public function findByUrl($url)
    {
        $folder = $this->backend->findByFinder(
            new FolderFinder(array('url' => $url))
        )->first()->getOrElse(null);

        return $folder;
    }

    public function createByUrl($url)
    {
        $folder = $this->findByUrl($url);
        if ($folder) {
            return $folder;
        }

        $rootFolder = $this->findRoot();

        $exploded = explode('/', $url);

        $folderNames = array();

        $created = null;
        $previous = null;

        while (sizeof($exploded) || !$created) {

            $folderNames[] = $folderCurrent = array_shift($exploded);
            $folderName = implode('/', $folderNames);
            $created = $this->findByUrl($folderName);

            if (!$created) {
                $created = Folder::create(
                    array(
                        'parent_id' => $previous ? $previous->getId() : $rootFolder->getId(),
                        'name' => $folderCurrent,
                    )
                );

                $this->create($created);
            }
            $previous = $created;
        }
        return $created;
    }

    /**
     * Finds subfolders
     *
     * @param  Folder        $folder
     * @return Sequence
     */
    public function findSubFolders(Folder $folder)
    {
        $folders = $this->backend->findByFinder(
            new FolderFinder(array('parent_id' => $folder->getId()))
        );

        return $folders;
    }

    /**
     * Finds parent folder
     *
     * @param  Folder       $folder
     * @return Folder
     */
    public function findParentFolder(Folder $folder)
    {
        if (!$folder->getParentId()) {
            return false;
        }

        $parent = $this->backend->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder');

        return $parent;
    }

    /**
     * @param  Folder        $folder Folder
     * @return Sequence
     */
    public function findFiles(Folder $folder)
    {
        $files = $this->backend->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId()))
        );

        return $files;
    }
}
