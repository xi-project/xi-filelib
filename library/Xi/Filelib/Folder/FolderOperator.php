<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder;

use Xi\Filelib\AbstractOperator;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\CommanderClient;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;

class FolderOperator extends AbstractOperator
{
    const COMMAND_CREATE = 'Xi\Filelib\Folder\Command\CreateFolderCommand';
    const COMMAND_DELETE = 'Xi\Filelib\Folder\Command\DeleteFolderCommand';
    const COMMAND_UPDATE = 'Xi\Filelib\Folder\Command\UpdateFolderCommand';
    const COMMAND_CREATE_BY_URL = 'Xi\Filelib\Folder\Command\CreateByUrlFolderCommand';

    /**
     * @return array
     */
    public function getCommandDefinitions()
    {
        return array(
            new CommandDefinition(self::COMMAND_CREATE),
            new CommandDefinition(self::COMMAND_CREATE_BY_URL),
            new CommandDefinition(self::COMMAND_DELETE),
            new CommandDefinition(self::COMMAND_UPDATE),
        );
    }

    /**
     * Returns directory route for folder
     *
     * @param  Folder $folder
     * @return string
     */
    public function buildRoute(Folder $folder)
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
        return $this->commander
            ->createExecutable(self::COMMAND_CREATE, array($folder))
            ->execute();
    }

    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     */
    public function delete(Folder $folder)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_DELETE, array($folder))
            ->execute();
    }

    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_UPDATE, array($folder))
            ->execute();
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
        )->current();

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
        $folder = new Folder();
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
        )->current();

        return $folder;
    }

    public function createByUrl($url)
    {
        return $this->commander
            ->createExecutable(self::COMMAND_CREATE_BY_URL, array($url))
            ->execute();
    }

    /**
     * Finds subfolders
     *
     * @param  Folder        $folder
     * @return ArrayIterator
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
        if (!$parentId = $folder->getParentId()) {
            return false;
        }

        $parent = $this->backend->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder');

        return $parent;
    }

    /**
     * @param  Folder        $folder Folder
     * @return ArrayIterator Collection of file items
     */
    public function findFiles(Folder $folder)
    {
        $files = $this->backend->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId()))
        );

        return $files;
    }
}
