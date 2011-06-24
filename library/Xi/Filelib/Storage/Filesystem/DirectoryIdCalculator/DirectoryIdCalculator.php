<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

interface DirectoryIdCalculator
{
    
    /**
     * Calculates directory id (path) for a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @return string
     */
    public function calculateDirectoryId(\Xi\Filelib\File\File $file);
}