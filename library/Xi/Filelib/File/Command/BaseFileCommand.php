<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Storage\Storage;

abstract class BaseFileCommand implements Command
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

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

    /**
     * @var ResourceRepository
     */
    protected $resourceRepository;

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->fileRepository = $filelib->getFileRepository();
        $this->resourceRepository = $filelib->getResourceRepository();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->backend = $filelib->getBackend();
        $this->storage = $filelib->getStorage();
    }
}
