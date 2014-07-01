<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;

abstract class BaseFolderCommand implements Command
{
    /**
     *
     * @var FolderRepository
     */
    protected $folderRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Backend
     */
    protected $backend;

    public function attachTo(FileLibrary $filelib)
    {
        $this->folderRepository = $filelib->getFolderRepository();
        $this->eventDispatcher = $filelib->getEventDispatcher();
        $this->backend = $filelib->getBackend();
    }
}
