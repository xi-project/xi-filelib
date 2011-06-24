<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

class TimeDirectoryIdCalculator extends AbstractDirectoryIdCalculator
{
    /**
     * @var string
     */
    private $_format = 'Y/m/d';
    
    
    /**
     * Sets directory creation format
     * 
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }
        
    /**
     * Returns directory creation format
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }
    
    public function calculateDirectoryId(\Xi\Filelib\File\File $file)
    {
        $dt = $file->getDateUploaded();
        
        if(!($dt instanceof \DateTime)) {
            throw new \Xi\Filelib\FilelibException("Upload date not set in file");
        }
        
        $path = $dt->format($this->getFormat());
        return $path;
    }
    
    
    
}
