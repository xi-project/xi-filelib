<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource\Command;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Storage\Storage;

abstract class BaseResourceCommand implements Command
{
    /**
     * @var ResourceRepository
     */
    protected $resourceRepository;

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
        $this->resourceRepository = $filelib->getResourceRepository();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->backend = $filelib->getBackend();
        $this->storage = $filelib->getStorage();
    }
}
