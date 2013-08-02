<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder;

use Xi\Filelib\AbstractOperator;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use ArrayIterator;

/**
 * Operates on folders
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
class FolderOperator extends AbstractOperator
{
    const COMMAND_CREATE = 'create';
    const COMMAND_DELETE = 'delete';
    const COMMAND_UPDATE = 'update';
    const COMMAND_CREATE_BY_URL = 'create_by_url';

    protected $commandStrategies = array(
        self::COMMAND_CREATE => EnqueueableCommand::STRATEGY_SYNCHRONOUS,
        self::COMMAND_DELETE => EnqueueableCommand::STRATEGY_SYNCHRONOUS,
        self::COMMAND_UPDATE => EnqueueableCommand::STRATEGY_SYNCHRONOUS,
        self::COMMAND_CREATE_BY_URL => EnqueueableCommand::STRATEGY_SYNCHRONOUS,
    );

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
        $command = $this->createCommand('Xi\Filelib\Folder\Command\CreateFolderCommand', array(
            $folder
        ));

        return $this->executeOrQueue($command, self::COMMAND_CREATE);
    }

    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     */
    public function delete(Folder $folder)
    {
        $command = $this->createCommand('Xi\Filelib\Folder\Command\DeleteFolderCommand', array(
            $folder
        ));

        return $this->executeOrQueue($command, self::COMMAND_DELETE);

    }

    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder)
    {
        $command = $this->createCommand('Xi\Filelib\Folder\Command\UpdateFolderCommand', array(
            $folder
        ));

        return $this->executeOrQueue($command, self::COMMAND_UPDATE);
    }

    /**
     * Finds the root folder
     *
     * @return Folder
     */
    public function findRoot()
    {
        $folder = $this->getBackend()->findByFinder(
            new FolderFinder(array('parent_id' => null))
        )->current();

        // @todo: remove side effect!!!
        if (!$folder) {
            $folder = new Folder();
            $folder->setName('root');
            $this->create($folder);
        }

        if (!$folder) {
            throw new FilelibException('Could not locate root folder', 500);
        }

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
        $folder = $this->getBackend()->findById($id, 'Xi\Filelib\Folder\Folder');

        return $folder;
    }

    public function findByUrl($url)
    {
        $folder = $this->getBackend()->findByFinder(
            new FolderFinder(array('url' => $url))
        )->current();

        return $folder;
    }

    public function createByUrl($url)
    {
        $command = $this->createCommand('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand', array(
            $url
        ));

        return $this->executeOrQueue($command, self::COMMAND_CREATE_BY_URL);
    }

    /**
     * Finds subfolders
     *
     * @param  Folder        $folder
     * @return ArrayIterator
     */
    public function findSubFolders(Folder $folder)
    {
        $folders = $this->getBackend()->findByFinder(
            new FolderFinder(array('parent_id' => $folder->getId()))
        );

        return $folders;
    }

    /**
     * Finds parent folder
     *
     * @param  Folder       $folder
     * @return Folder|false
     */
    public function findParentFolder(Folder $folder)
    {
        if (!$parentId = $folder->getParentId()) {
            return false;
        }

        $parent = $this->getBackend()->findById($folder->getParentId(), 'Xi\Filelib\Folder\Folder');

        return $parent;
    }

    /**
     * @param  Folder        $folder Folder
     * @return ArrayIterator Collection of file items
     */
    public function findFiles(Folder $folder)
    {
        $files = $this->getBackend()->findByFinder(
            new FileFinder(array('folder_id' => $folder->getId()))
        );

        return $files;
    }

    /**
     *
     * @return FileOperator
     */
    public function getFileOperator()
    {
        return $this->getFilelib()->getFileOperator();
    }
}
