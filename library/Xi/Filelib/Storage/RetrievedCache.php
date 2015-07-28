<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

class RetrievedCache
{
    private $retrieved;

    private $retrievedVersions;

    /**
     * @param Resource $resource
     * @return Retrieved
     */
    public function get(Resource $resource)
    {
        return (isset($this->retrieved[$resource->getId()])) ? $this->retrieved[$resource->getId()] : false;
    }

    public function set(Resource $resource, Retrieved $retrieved)
    {
        $this->retrieved[$resource->getId()] = $retrieved;
    }

    public function delete(Resource $resource)
    {
        if (isset($this->retrieved[$resource->getId()])) {
            unset($this->retrieved[$resource->getId()]);
        }
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return Retrieved
     */
    public function getVersion(Versionable $versionable, Version $version)
    {
        return (isset($this->retrievedVersions[get_class($versionable)][$versionable->getId()][$version->toString()]))
            ? $this->retrievedVersions[get_class($versionable)][$versionable->getId()][$version->toString()] : false;
    }

    public function setVersion(Versionable $versionable, Version $version, Retrieved $retrieved)
    {
        $this->retrievedVersions[get_class($versionable)][$versionable->getId()][$version->toString()] = $retrieved;
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        if (isset($this->retrievedVersions[get_class($versionable)][$versionable->getId()][$version->toString()])) {
            unset($this->retrievedVersions[get_class($versionable)][$versionable->getId()][$version->toString()]);
        }
    }
}
