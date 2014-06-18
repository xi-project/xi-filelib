<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Attacher;

/**
 * Publisher adapter interface
 *
 * @author pekkis
 *
 */
interface PublisherAdapter extends Attacher
{
    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return bool
     */
    public function publish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker);

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return bool
     */
    public function unpublish(File $file, Version $version, VersionProvider $versionProvider, Linker $linker);

    /**
     * @param File $file
     * @param Version $version
     * @param VersionProvider $versionProvider
     * @param Linker $linker
     * @return string
     */
    public function getUrl(File $file, Version $version, VersionProvider $versionProvider, Linker $linker);
}
