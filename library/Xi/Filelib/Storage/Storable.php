<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

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
     * @param string $version
     * @return self
     */
    public function addVersion($version);

    /**
     * @param string $version
     * @return self
     */
    public function removeVersion($version);

    /**
     * @param string $version
     * @return bool
     */
    public function hasVersion($version);
}
