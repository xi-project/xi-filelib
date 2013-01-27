<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\FileLibrary\File\FileOperator;
use Xi\Filelib\FileLibrary\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Command;
use ReflectionObject;

/**
 * Convenience base class for queue processors
 */
abstract class AbstractQueueProcessor implements QueueProcessor
{

    /**
     *
     * @var FileLibrary
     */
    protected $filelib;

    /**
     *
     * @var FileOperator
     */
    protected $fileOperator;

    /**
     *
     * @var FolderOperator
     */
    protected $folderOperator;

    /**
     *
     * @var Queue
     */
    protected $queue;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
        $this->fileOperator = $filelib->getFileOperator();
        $this->folderOperator = $filelib->getFolderOperator();
        $this->queue = $filelib->getQueue();
    }

    /**
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    /**
     *
     * @return FileOperator
     */
    public function getFileOperator()
    {
        return $this->fileOperator;
    }

    /**
     *
     * @return FolderOperator
     */
    public function getFolderOperator()
    {
        return $this->folderOperator;
    }

    /**
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Injects dependencies to commands via black reflection magic. You know,
     * dependencies can not be transferred via network.
     *
     * @param Command $command
     */
    public function injectOperators(Command $command)
    {
        $refl = new ReflectionObject($command);

        if ($refl->hasProperty('fileOperator')) {

            $prop = $refl->getProperty('fileOperator');
            $prop->setAccessible(true);
            $prop->setValue($command, $this->getFileOperator());
            $prop->setAccessible(false);
        }

        if ($refl->hasProperty('folderOperator')) {
            $prop = $refl->getProperty('folderOperator');
            $prop->setAccessible(true);
            $prop->setValue($command, $this->getFolderOperator());
            $prop->setAccessible(false);
        }
    }

}
