<?php

namespace Xi\Filelib\File;

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

    /**
     * Returns filename, overridden if defined, default if not
     *
     * @return string Filename
     *
     */
    public function getOverrideFilename()
    {
        return ($this->_overrideFilename) ? $this->_overrideFilename : $this->getFilename();
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
        if($this->isTemporary()) {
            unlink($this->getRealPath());
        }
    }
    
    

}