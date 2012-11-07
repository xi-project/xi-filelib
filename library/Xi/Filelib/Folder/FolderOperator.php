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
     * @param Folder $folder
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
     * Returns an instance of the currently set folder class
     *
     * @param array $data Data
     */
    public function getInstance(array $data = array())
    {
        $folder = new Folder();
        if ($data) {
            $folder->fromArray($data);
        }
        return $folder;
    }

    /**
     * Creates a folder
     *
     * @param Folder $folder
     */
    public function create(Folder $folder)
    {
        $command = $this->createCommand('Xi\Filelib\Folder\Command\CreateFolderCommand', array(
            $this, $folder
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
            $this, $this->getFileOperator(), $folder
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
            $this, $this->getFileOperator(), $folder
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
        $folder = $this->getBackend()->findRootFolder();

        if (!$folder) {
            throw new FilelibException('Could not locate root folder', 500);
        }

        $folder = $this->getInstance($folder);

        return $folder;
    }

    /**
     * Finds a folder
     *
     * @param mixed $id Folder id
     * @return Folder
     */
    public function find($id)
    {
        $folder = $this->getBackend()->findFolder($id);
        if (!$folder) {
            return false;
        }

        $folder = $this->getInstance($folder);
        return $folder;
    }

    public function findByUrl($url)
    {
        $folder = $this->getBackend()->findFolderByUrl($url);

        if (!$folder) {
            return false;
        }

        $folder = $this->getInstance($folder);
        return $folder;
    }

    public function createByUrl($url)
    {
        $command = $this->createCommand('Xi\Filelib\Folder\Command\CreateByUrlFolderCommand', array(
            $this, $url
        ));
        return $this->executeOrQueue($command, self::COMMAND_CREATE_BY_URL);
    }

    /**
     * Finds subfolders
     *
     * @param Folder $folder
     * @return ArrayIterator
     */
    public function findSubFolders(Folder $folder)
    {
        $rawFolders = $this->getBackend()->findSubFolders($folder);

        $folders = array();
        foreach ($rawFolders as $rawFolder) {
            $folder = $this->getInstance($rawFolder);
            $folders[] = $folder;
        }
        return new ArrayIterator($folders);
    }

    /**
     * Finds parent folder
     *
     * @param Folder $folder
     * @return Folder|false
     */
    public function findParentFolder(Folder $folder)
    {
        if (!$parentId = $folder->getParentId()) {
            return false;
        }

        $parent = $this->getBackend()->findFolder($parentId);

        if (!$parent) {
            return false;
        }

        return $this->getInstance($parent);
    }

    /**
     * @param Folder $folder Folder
     * @return ArrayIterator Collection of file items
     */
    public function findFiles(Folder $folder)
    {
        $ritems = $this->getBackend()->findFilesIn($folder);

        $items = array();
        foreach ($ritems as $ritem) {
            $item = $this->getFileOperator()->getInstanceAndTriggerEvent($ritem);
            $items[] = $item;
        }

        return new ArrayIterator($items);
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
