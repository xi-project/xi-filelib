<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Command\Command;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\FileLibrary;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFolderCommand implements Command
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
