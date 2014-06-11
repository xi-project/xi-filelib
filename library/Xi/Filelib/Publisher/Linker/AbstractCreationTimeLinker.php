<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Linker;

/**
 * Calculates directory id by formatting an objects creation date
 */
abstract class AbstractCreationTimeLinker
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
     * @param File $file
     * @param string $version
     * @param string $extension
     * @return string
     */
    public function getLink(File $file, $version, $extension)
    {
        $pinfo = pathinfo($this->getFileName($file));

        return $file->getDateCreated()->format($this->getFormat()) . '/' . $pinfo['filename']
           . '-' . $version . '.' . $extension;
    }

    /**
     * @param File $file
     * @return string
     */
    abstract protected function getFileName(File $file);
}
