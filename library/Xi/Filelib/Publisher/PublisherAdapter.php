<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\FileLibrary;

/**
 * Publisher adapter interface
 *
 * @author pekkis
 *
 */
interface PublisherAdapter
{
    /**
     * Publishes a file
     *
     * @param File $file
     * @return bool
     */
    public function publish(File $file, Linker $linker);

    /**
     * @param File $file
     * @param VersionProvider $version
     * @param Linker $linker
     * @return bool
     */
    public function publishVersion(File $file, VersionProvider $version, Linker $linker);

    /**
     * Unpublishes a file
     *
     * @param File $file
     * @return bool
     */
    public function unpublish(File $file, Linker $linker);

    /**
     * @param File $file
     * @param VersionProvider $version
     * @param Linker $linker
     * @return bool
     */
    public function unpublishVersion(File $file, VersionProvider $version, Linker $linker);

    /**
     * Returns url to a file
     *
     * @param  File   $file
     * @return string
     */
    public function getUrl(File $file, Linker $linker);

    /**
     * @param File $file
     * @param VersionProvider $version
     * @param Linker $linker
     * @return string
     */
    public function getUrlVersion(File $file, VersionProvider $version, Linker $linker);

    public function setDependencies(FileLibrary $filelib);
}
