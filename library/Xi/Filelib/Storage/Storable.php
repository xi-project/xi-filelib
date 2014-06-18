<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Plugin\VersionProvider\Version;

interface Storable
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return array
     */
    public function getVersions();

    /**
     * @param Version $version
     * @return self
     */
    public function addVersion(Version $version);

    /**
     * @param Version $version
     * @return self
     */
    public function removeVersion(Version $version);

    /**
     * @param Version $version
     * @return bool
     */
    public function hasVersion(Version $version);
}
