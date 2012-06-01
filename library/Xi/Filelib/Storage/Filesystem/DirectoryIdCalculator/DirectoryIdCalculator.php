<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\File\Resource;

interface DirectoryIdCalculator
{

    /**
     * Calculates directory id (path) for a file
     *
     * @param object $resource
     * @return string
     */
    public function calculateDirectoryId($resource);
}