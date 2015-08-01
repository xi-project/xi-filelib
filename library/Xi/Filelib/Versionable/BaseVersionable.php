<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Versionable;

use Xi\Filelib\BaseIdentifiable;
use Xi\Filelib\Resource\ConcreteResource;

abstract class BaseVersionable extends BaseIdentifiable implements Versionable
{
    /**
     * @var array
     */
    private $versions = [];

    /**
     * Returns currently created versions
     *
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }

    public function getVersion($version)
    {
        return $this->versions[Version::get($version)->toString()];
    }

    /**
     * Adds version
     *
     * @param mixed $version
     * @return self
     */
    public function addVersion($version, ConcreteResource $resource)
    {
        $versioned = new Versioned($version, $resource);
        $this->versions[$versioned->getVersion()->toString()] = $versioned;

        return $this;
    }

    /**
     * Removes a version
     *
     * @param mixed $version
     * @return self
     */
    public function removeVersion($version)
    {
        $version = Version::get($version);
        unset($this->versions[$version->toString()]);

        return $this;
    }

    /**
     * Returns whether resource has version
     *
     * @param mixed $version
     * @return boolean
     */
    public function hasVersion($version)
    {
        $version = Version::get($version);
        return isset($this->versions[$version->toString()]);
    }

    /**
     * @param array $versions
     * @return self
     */
    protected function setVersions($versions)
    {
        $this->getData()->set('versions', $versions);
        return $this;
    }
}
