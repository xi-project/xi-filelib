<?php

namespace Xi\Filelib\Storage;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Version;

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
     * @param Storable $storable
     * @param Version $version
     * @return Retrieved
     */
    public function getVersion(Storable $storable, Version $version)
    {
        return (isset($this->retrievedVersions[get_class($storable)][$storable->getId()][$version->toString()]))
            ? $this->retrievedVersions[get_class($storable)][$storable->getId()][$version->toString()] : false;
    }

    public function setVersion(Storable $storable, Version $version, Retrieved $retrieved)
    {
        $this->retrievedVersions[get_class($storable)][$storable->getId()][$version->toString()] = $retrieved;
    }

    public function deleteVersion(Storable $storable, Version $version)
    {
        if (isset($this->retrievedVersions[get_class($storable)][$storable->getId()][$version->toString()])) {
            unset($this->retrievedVersions[get_class($storable)][$storable->getId()][$version->toString()]);
        }
    }
}
