<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileEvent;
use Serializable;

class PublishFileCommand extends AbstractFileCommand implements Serializable
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
        $profile = $this->fileOperator->getProfile($this->file->getProfile());
        if ($profile->getPublishOriginal()) {
            $this->fileOperator->getPublisher()->publish($this->file);
        }
        $event = new FileEvent($this->file);
        $this->fileOperator->getEventDispatcher()->dispatch('file.publish', $event);

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