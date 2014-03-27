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

class AfterUploadFileCommand extends AbstractFileCommand
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function execute()
    {
        if (!$this->file instanceof File) {
            $this->file = $this->fileRepository->find($this->file->getId());
        }

        $event = new FileEvent($this->file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_AFTERUPLOAD, $event);

        // @todo: actual statuses
        $this->file->setStatus(File::STATUS_COMPLETED);

        // @todo: fucktor.
        $this->backend->updateFile($this->file);
        return $this->file;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.after_upload';
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
                'file' => $this->file->getId(),
            )
        );
    }
}
