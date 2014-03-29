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

class UpdateFileCommand extends AbstractFileCommand
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

        $this->backend->updateFile($this->file);

        $event = new FileEvent($this->file);
        $this->eventDispatcher->dispatch(Events::FILE_AFTER_UPDATE, $event);

        return $this->file;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.file.update';
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
