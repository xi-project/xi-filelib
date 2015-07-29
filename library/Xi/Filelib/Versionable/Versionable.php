<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Versionable;

use DateTime;
use Xi\Filelib\Resource\Resource;

interface Versionable
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getUuid();

    /**
     * @return DateTime
     */
    public function getDateCreated();

    /**
     * @return array
     */
    public function getVersions();

    public function addVersion($version, Resource $resource);

    public function removeVersion($version);

    /**
     * @param $version
     * @return Versioned
     */
    public function getVersion($version);

    /**
     * @param mixed $version
     * @return bool
     */
    public function hasVersion($version);
}
