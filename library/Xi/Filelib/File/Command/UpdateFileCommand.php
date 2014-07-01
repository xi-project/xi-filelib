<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\ResourceRepository;

class UpdateFileCommand extends BaseFileCommand
{
    /**
     *
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function execute()
    {
        $event = new FileEvent($this->file);
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_UPDATE, $event);

        $this->resourceRepository->createCommand(
            ResourceRepository::COMMAND_UPDATE,
            array(
                $this->file->getResource()
            )
        )->execute();

        $this->backend->updateFile($this->file);

        $event = new FileEvent($this->file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UPDATE, $event);

        return $this->file;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.update';
    }
}
