<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Events;
use Pekkis\Queue\Message;

class DeleteFileCommand extends AbstractFileCommand
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
        $this->eventDispatcher->dispatch(Events::FILE_BEFORE_DELETE, $event);

        $this->backend->deleteFile($this->file);

        if ($this->file->getResource()->isExclusive()) {
            $this->storage->delete($this->file->getResource());
            $this->backend->deleteResource($this->file->getResource());
        }

        $event = new FileEvent($this->file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_DELETE, $event);

        return true;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.delete';
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->file = $data['file'];
    }

    public function serialize()
    {
        return serialize(
            array(
                'file' => $this->file,
            )
        );
    }
}
