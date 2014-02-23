<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Storage\Storage;

abstract class AbstractFileCommand implements Command
{
    /**
     * @var FileOperator
     */
    protected $fileOperator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * @var Storage
     */
    protected $storage;

    public function attachTo(FileLibrary $filelib)
    {
        $this->fileOperator = $filelib->getFileOperator();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->backend = $filelib->getBackend();
        $this->storage = $filelib->getStorage();
    }
}
