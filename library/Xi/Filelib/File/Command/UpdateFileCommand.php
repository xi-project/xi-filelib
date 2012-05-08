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

class UpdateFileCommand extends AbstractFileCommand implements Serializable
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
        $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\UnpublishFileCommand', array($this->fileOperator, $this->file));
        $command->execute();
                
        $linker = $this->fileOperator->getProfile($this->file->getProfile())->getLinker();

        $this->file->setLink($linker->getLink($this->file, true));

        $this->fileOperator->getBackend()->updateFile($this->file);

        if ($this->fileOperator->getAcl()->isFileReadableByAnonymous($this->file)) {
            
            $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\PublishFileCommand', array($this->fileOperator, $this->file));
            $command->execute();
            
        }

        return $this->file;

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
