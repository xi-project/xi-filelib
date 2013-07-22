<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\Plugin;

/**
 * Interface for version providing plugins
 *
 * @author pekkis
 */
interface VersionProvider extends Plugin
{
    /**
     * Returns file extension for a version
     *
     * @param string $version
     */
    public function getExtensionFor(File $file, $version);

    /**
     * Returns file types which the version plugin provides version for.
     *
     * @return array
     */
    public function getProvidesFor();

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function providesFor(File $file);

    /**
     * Returns version identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Returns an array of (potentially) provided versions
     *
     * @return array
     */
    public function getVersions();

    /**
     * Returns whether versions are already created for a resource
     *
     * @return bool
     */
    public function areVersionsCreated(File $file);

    /**
     * Returns whether plugin allows sharing of resources
     */
    public function isSharedResourceAllowed();

    public function areSharedVersionsAllowed();
}
