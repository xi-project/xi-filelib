<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Plugin\VersionProvider\Version;

abstract class BaseStorable extends BaseIdentifiable
{
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
     * @param Version $version
     * @return self
     */
    public function addVersion(Version $version)
    {
        $versions = $this->getVersions();
        if (!in_array($version->toString(), $versions)) {
            array_push($versions, $version->toString());
            $this->setVersions($versions);
        }

        return $this;
    }

    /**
     * Removes a version
     *
     * @param Version $version
     * @return self
     */
    public function removeVersion(Version $version)
    {
        $versions = $this->getVersions();
        $versions = array_diff($versions, array($version->toString()));
        return $this->setVersions($versions);
    }

    /**
     * Returns whether resource has version
     *
     * @param Version $version
     * @return boolean
     */
    public function hasVersion(Version $version)
    {
        return in_array($version, $this->getVersions());
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
