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
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function providesFor(File $file);

    /**
     * Returns an array of versions provided
     *
     * @return array
     */
    public function getVersions();

    /**
     * (Re)create and store all versions provided by the plugin
     */
    public function createVersions(File $file);

    /**
     * Returns whether all versions are already created for a resource
     *
     * @return bool
     */
    public function areVersionsCreated(File $file);

    /**
     * Returns whether plugin allows sharing of resources
     *
     * @return bool
     */
    public function isSharedResourceAllowed();

    /**
     * @return bool
     */
    public function areSharedVersionsAllowed();
}
