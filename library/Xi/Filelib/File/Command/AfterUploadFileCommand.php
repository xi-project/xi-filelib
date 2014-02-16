<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
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
        $file = $this->file;

        $event = new FileEvent($file);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_AFTER_AFTERUPLOAD, $event);

        // @todo: actual statuses
        $file->setStatus(File::STATUS_COMPLETED);

        $this->fileOperator->getBackend()->updateFile($file);

        return $file;
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
                'file' => $this->file,
            )
        );
    }
}
