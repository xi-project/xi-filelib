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

class UploadFileCommand extends AbstractFileCommand implements Serializable
{
    /**
     *
     * @var FileUpload
     */
    private $upload;
    
    /**
     *
     * @var Folder
     */
    private $folder;
    
    /**
     *
     * @var string
     */
    private $profile;    
    
    public function __construct(FileOperator $fileOperator, $upload, Folder $folder, $profile = 'default')
    {
        parent::__construct($fileOperator);
                       
        if (!$upload instanceof FileUpload) {
            $upload = $fileOperator->prepareUpload($upload);
        }

        $this->upload = $upload;
        $this->folder = $folder;
        $this->profile = $profile;
    }
    
    
    
    public function execute()
    {
        $upload = $this->upload;
        $folder = $this->folder;
        $profile = $this->profile;

        if (!$this->fileOperator->getAcl()->isFolderWritable($folder)) {
            throw new FilelibException("Folder '{$folder->getId()}'not writable");
        }

        $profileObj = $this->fileOperator->getProfile($profile);

        $event = new FileUploadEvent($upload, $folder, $profileObj);
        $this->fileOperator->getEventDispatcher()->dispatch('file.beforeUpload', $event);
        
        $upload = $event->getFileUpload();
        
        $file = $this->fileOperator->getInstance(array(
            'folder_id' => $folder->getId(),
            'mimetype' => $upload->getMimeType(),
            'size' => $upload->getSize(),
            'name' => $upload->getUploadFilename(),
            'profile' => $profile,
            'date_uploaded' => $upload->getDateUploaded(),
        ));

        // @todo: actual statuses
        $file->setStatus(File::STATUS_RAW);
        
        $this->fileOperator->getBackend()->upload($file, $folder);
        $this->fileOperator->getStorage()->store($file, $upload->getRealPath());
        
        $command = $this->fileOperator->createCommand('Xi\Filelib\File\Command\AfterUploadFileCommand', array($this->fileOperator, $file));
        return $command;
        
    }
    
    
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        
        $this->folder = $data['folder'];
        $this->profile = $data['profile'];
        
        $upload = new FileUpload($data['upload']['realPath']);
        $upload->setOverrideBasename($data['upload']['overrideBasename']);
        $upload->setOverrideFilename($data['upload']['overrideFilename']);
        $upload->setTemporary($data['upload']['temporary']);
        
        $this->upload = $upload;
        
    }
    
    
    public function serialize()
    {
        $upload = $this->upload;
        
        $uploadArr = array(
            'overrideBasename' => $upload->getOverrideBasename(),
            'overrideFilename' => $upload->getOverrideFilename(),
            'temporary' => $upload->isTemporary(),
            // 'dateUploaded' => $upload->getDateUploaded(),
            'realPath' => $upload->getRealPath(),
        );
        
        
        return serialize(array(
           'folder' => $this->folder,
           'profile' => $this->profile,
           'upload' => $uploadArr,
        ));
                
    }
    
    
    
}
