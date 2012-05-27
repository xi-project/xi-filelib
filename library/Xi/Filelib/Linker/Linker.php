<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Linker;

use \Xi\Filelib\FileLibrary,
    \Xi\Filelib\File\File,
    \Xi\Filelib\Plugin\VersionProvider\VersionProvider
    ;

/**
 * Linker interface
 *
 * @author pekkis
 *
 */
interface Linker
{

    /**
     * Sets filelib
     *
     * @return Linker
     */
    public function setFilelib(FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();

    /**
     * Returns link for a version of a file
     *
     * @param File $file
     * @param string $version Version identifier
     * @param string $extension Extension
     * @return string Versioned link
     */
    public function getLinkVersion(File $file, $version, $extension);

    /**
     * Returns a link for a file
     *
     * @param File $file
     * @return string Link
     */
    public function getLink(File $file);
}
