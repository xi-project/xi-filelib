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

class AfterUploadFileCommand extends AbstractFileCommand
{

    /**
     *
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function execute()
    {

        $file = $this->file;

        $profileObj = $this->fileOperator->getProfile($file->getProfile());

        $event = new FileEvent($file);
        $this->fileOperator->getEventDispatcher()->dispatch(Events::FILE_AFTER_AFTERUPLOAD, $event);

        // @todo: actual statuses
        $file->setStatus(File::STATUS_COMPLETED);

        $this->fileOperator->getBackend()->updateFile($file);

        return $file;
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->file = $data['file'];
        $this->uuid = $data['uuid'];
    }

    public function serialize()
    {
        return serialize(array(
            'file' => $this->file,
            'uuid' => $this->uuid,
        ));

    }

}
