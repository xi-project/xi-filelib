<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

abstract class BaseStorable extends BaseIdentifiable
{
    /**
     * Sets currently created versions
     *
     * @param  array    $versions
     * @return Resource
     */
    public function setVersions(array $versions = array())
    {
        $this->getData()->set('versions', $versions);
        return $this;
    }

    /**
     * Returns currently created versions
     *
     * @return array
     */
    public function getVersions()
    {
        return $this->getData()->get('versions', array());
    }

    /**
     * Adds version
     *
     * @param string $version
     */
    public function addVersion($version)
    {
        $versions = $this->getVersions();
        if (!in_array($version, $versions)) {
            array_push($versions, $version);
            $this->setVersions($versions);
        }
    }

    /**
     * Removes a version
     *
     * @param string $version
     */
    public function removeVersion($version)
    {
        $versions = $this->getVersions();
        $versions = array_diff($versions, array($version));
        $this->setVersions($versions);
    }

    /**
     * Returns whether resource has version
     *
     * @param  string  $version
     * @return boolean
     */
    public function hasVersion($version)
    {
        return in_array($version, $this->getVersions());
    }
}
