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
 */
class CreationTimeLinker implements Linker
{
    /**
     * @param string $format
     */
    public function __construct($format = 'Y/m/d')
    {
        $this->format = $format;
    }

    /**
     * @var string
     */
    private $format = 'Y/m/d';

    /**
     * Returns directory creation format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns link for a version of a file
     *
     * @param  File   $file
     * @param  string $version   Version identifier
     * @param  string $extension Extension
     * @return string Versioned link
     */
    public function getLink(File $file, $version, $extension)
    {
        return $file->getDateCreated()->format($this->getFormat())
            . '/' . $file->getName() . '-' . $version . '.' . $extension;
    }

    public function attachTo(FileLibrary $filelib)
    {

    }
}
