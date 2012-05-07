<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FilelibException;
use Serializable;

use Xi\Filelib\File\Command\UnpublishFileCommand;

class DeleteFileCommand extends AbstractFileCommand implements Serializable
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
        $command = new UnpublishFileCommand($this->fileOperator, $this->file);
        $command->execute();
                                        
        $this->fileOperator->getBackend()->deleteFile($this->file);
        $this->fileOperator->getStorage()->delete($this->file);

        $event = new FileEvent($this->file);
        $this->fileOperator->getEventDispatcher()->dispatch('file.delete', $event);
        
        return true;

    }
        
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->file = $data['file'];
    }
    
    
    public function serialize()
    {
        return serialize(array(
           'file' => $this->file,
        ));
                
    }
    
    
    
}
