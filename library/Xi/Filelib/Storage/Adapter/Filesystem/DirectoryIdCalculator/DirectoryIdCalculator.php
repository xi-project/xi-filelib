<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;

interface DirectoryIdCalculator
{
    /**
     * Calculates directory id (path) for a resource or a file
     *
     * @param Resource|File $obj
     * @return string
     */
    public function calculateDirectoryId($obj);
}
