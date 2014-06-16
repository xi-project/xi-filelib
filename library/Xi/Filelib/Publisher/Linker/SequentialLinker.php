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
 * The old non-reversible sequential linker
 *
 * @author pekkis
 * @author Petri Mahanen
 * @deprecated
 */
class SequentialLinker extends BaseSequentialLinker implements Linker
{
    /**
     * @param File $file
     * @return string
     */
    public function getFileName(File $file)
    {
        return $file->getName();
    }

    public function attachTo(FileLibrary $filelib)
    {

    }
}
