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
use Xi\Filelib\Storage\Storable;

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
    public function getExtension(File $file, $version);

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function isApplicableTo(File $file);

    /**
     * Returns an array of versions provided
     *
     * @return array
     */
    public function getProvidedVersions();

    /**
     * (Re)create and store all versions provided by the plugin
     */
    public function createProvidedVersions(File $file);

    /**
     * Deletes all provided versions of a storable
     *
     * @param Storable $storable
     */
    public function deleteProvidedVersions(Storable $storable);

    /**
     * Returns whether all versions are already created for a resource
     *
     * @return bool
     */
    public function areProvidedVersionsCreated(File $file);

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

    /**
     * @return bool
     */
    public function canBeLazy();
}
