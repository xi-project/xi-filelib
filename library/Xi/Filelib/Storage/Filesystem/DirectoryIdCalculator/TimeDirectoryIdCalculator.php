<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use \DateTime,
    Xi\Filelib\FilelibException,
    Xi\Filelib\File\File
    ;

class TimeDirectoryIdCalculator extends AbstractDirectoryIdCalculator
{
    /**
     * @var string
     */
    private $format = 'Y/m/d';
    
    
    /**
     * Sets directory creation format
     * 
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
        
    /**
     * Returns directory creation format
     * 
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
    
    public function calculateDirectoryId(File $file)
    {
        $dt = $file->getDateUploaded();
        
        if(!($dt instanceof DateTime)) {
            throw new FilelibException("Upload date not set in file");
        }
        
        $path = $dt->format($this->getFormat());
        return $path;
    }
    
    
    
}
