<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\Upload\FileUpload;
use Serializable;

class AfterUploadFileCommand extends AbstractFileCommand implements Serializable
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
        $file = $this->file;

        $profileObj = $this->fileOperator->getProfile($file->getProfile());

        $event = new FileEvent($file);
        $this->fileOperator->getEventDispatcher()->dispatch('file.afterUpload', $event);

        // @todo: actual statuses
        $file->setStatus(File::STATUS_COMPLETED);
        $file->setLink($profileObj->getLinker()->getLink($file, true));
        $this->fileOperator->getBackend()->updateFile($file);

        if ($this->fileOperator->getAcl()->isFileReadableByAnonymous($file)) {

            $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\PublishFileCommand', array($this->fileOperator, $this->file));
            $command->execute();
        }

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
