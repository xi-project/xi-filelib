<?php

namespace Xi\Filelib\File\Upload;

use \Xi\Filelib\Folder\Folder;

/**
 * Uploadable file
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
class FileUpload extends \Xi\Filelib\File\FileObject
{
    /**
     * @var string Override filename
     */
    private $_overrideFilename;

    private $_overrideBasename;
        
    /**
     * @var \Xi_Filelib_Filelib
     */
    private $_filelib;

    /**
     * @var \DateTime
     */
    private $_dateUploaded;

    
    private $_temporary = false;
    
    
    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }

    /**
     * Overrides real filename
     *
     * @param string Overriding filename
     */
    public function setOverrideFilename($filename)
    {
        $this->_overrideFilename = $filename;
    }
    
    public function setOverrideBasename($basename)
    {
        $this->_overrideBasename = $basename;
    }
    
    
    public function getOverrideBasename()
    {
        return $this->_overrideBasename;
    }
    
    
    public function getUploadFilename()
    {
        if(!$uploadName = $this->getOverrideFilename()) {
            $uploadName = $this->getFilename();
        }
        
        if (!$overrideBase = $this->getOverrideBasename()) {
            return $uploadName;
        }
        
        
        $pinfo = pathinfo($uploadName);

        
        $uploadName = $overrideBase;
        if (isset($pinfo['extension']) && $pinfo['extension'])  {
            $uploadName .= '.' . $pinfo['extension'];
        }

        return $uploadName;
    }
    
    

    /**
     * Returns filename, overridden if defined, default if not
     *
     * @return string Filename
     *
     */
    public function getOverrideFilename()
    {
        return $this->_overrideFilename;
    }
    
    
    /**
     * Returns upload date
     * 
     * @return \DateTime
     */
    public function getDateUploaded()
    {
        if(!$this->_dateUploaded) {
            $this->_dateUploaded = new \DateTime();
        }
        return $this->_dateUploaded;
    }
    
    /**
     * Sets upload date
     * 
     * @param \DateTime $dateUploaded
     */
    public function setDateUploaded(\DateTime $dateUploaded)
    {
        $this->_dateUploaded = $dateUploaded;
    }
    
    
    
    public function setTemporary($temporary)
    {
        $this->_temporary = $temporary;
    }

    
    public function isTemporary()
    {
        return $this->_temporary;
    }

    
    public function __destruct()
    {
        if ($this->isTemporary()) {
            unlink($this->getRealPath());
        }
    }
    
    
    public function upload(Folder $folder, $profile = 'default')
    {
        
        if (!$this->getFilelib()->getAcl()->isWriteable($folder)) {
            throw new \Xi\Filelib\FilelibException("Folder '{$folder->getId()}'not writeable");
        }
        
        $profileObj = $this->getFilelib()->file()->getProfile($profile);
        foreach($profileObj->getPlugins() as $plugin) {
            $upload = $plugin->beforeUpload($this);
        }

        $file = $this->getFilelib()->getFileOperator()->getInstance(array(
            'folder_id' => $folder->getId(),
            'mimetype' => $upload->getMimeType(),
            'size' => $upload->getSize(),
            'name' => $upload->getUploadName(),
            'profile' => $profile,
            'date_uploaded' => $upload->getDateUploaded(),
        ));
        
        $this->getFilelib()->getBackend()->upload($file, $folder);
        
        // @todo: identity map, unit o work etc
        if(!$file->getId()) {
            throw new \Xi\Filelib\FilelibException("Upload failed");
        }

        // $file = $this->getFilelib()->getFileOperator()->getInstance($file);
        
        $file->setLink($profile->getLinker()->getLink($file, true));
        
        $this->getFilelib()->getBackend()->updateFile($file);
        
        try {
            
            $this->getFilelib()->getStorage()->store($file, $this->getRealPath());
            
            foreach($file->getProfileObject()->getPlugins() as $plugin) {
                $upload = $plugin->afterUpload($file);
            }
            
            if($this->getFilelib()->getAcl()->isReadableByAnonymous($file)) {
                $this->getFilelib()->getFileOperator()->publish($file);
            }
            
        } catch(Exception $e) {
            
            // Maybe log here?
            throw $e;
        }


        return $file;

    }
    
    

}