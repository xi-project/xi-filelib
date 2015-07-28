<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\BaseStorageAdapter;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

/**
 * Faking in memory adapter
 *
 */
class MemoryStorageAdapter extends BaseStorageAdapter
{
    private $resources = [];

    public function __construct()
    {

    }

    /**
     * @param Resource $resource
     * @param string $tempFile
     */
    public function store(Resource $resource, $tempFile)
    {
        $this->resources[$resource->getId()] = $tempFile;
    }

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $this->resources[$versionable->getId() . ';' . $version->toString()] = $tempFile;
    }

    public function retrieve(Resource $resource)
    {
        return new Retrieved($this->resources[$resource->getId()], false);
    }

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        return new Retrieved(
            $this->resources[$versionable->getId() . ';' . $version->toString()],
            false
        );
    }

    public function delete(Resource $resource)
    {
        unset($this->resources[$resource->getId()]);
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        unset($this->resources[$versionable->getId() . ';' . $version->toString()]);
    }

    public function exists(Resource $resource)
    {
        return isset($this->resources[$resource->getId()]);

    }

    public function versionExists(Versionable $versionable, Version $version)
    {
        return isset($this->resources[$versionable->getId() . ';' . $version->toString()]);
    }
}
