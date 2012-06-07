<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
