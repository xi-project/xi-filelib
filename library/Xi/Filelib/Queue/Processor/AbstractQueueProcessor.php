<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
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
     * @var Queue
     */
    protected $queue;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
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
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

}
