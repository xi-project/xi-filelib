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

class UnpublishFileCommand extends AbstractFileCommand
{

    /**
     *
     * @var File
     */
    private $file;


    public function __construct(FileOperator $fileOperator, File $file)
    {
        parent::__construct($fileOperator);
        $this->file = $file;
    }

    public function execute()
    {
        $this->fileOperator->getPublisher()->unpublish($this->file);
        $event = new FileEvent($this->file);
        $this->fileOperator->getEventDispatcher()->dispatch('xi_filelib.file.unpublish', $event);
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
