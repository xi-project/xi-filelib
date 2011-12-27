<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\File\File;

interface DirectoryIdCalculator
{
    
    /**
     * Calculates directory id (path) for a file
     * 
     * @param File $file
     * @return string
     */
    public function calculateDirectoryId(File $file);
}