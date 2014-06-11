<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Linker;

/**
 * Calculates directory id by formatting an objects creation date
 *
 * @deprecated
 */
class CreationTimeLinker extends AbstractCreationTimeLinker implements Linker
{
    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {

    }

    /**
     * @param File $file
     * @return string
     */
    protected function getFileName(File $file)
    {
        return $file->getName();
    }
}
