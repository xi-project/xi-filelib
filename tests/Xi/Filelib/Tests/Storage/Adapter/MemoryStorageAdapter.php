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

    public function retrieve(Resource $resource)
    {
        return new Retrieved($this->resources[$resource->getId()], false);
    }

    public function delete(Resource $resource)
    {
        unset($this->resources[$resource->getId()]);
    }

    public function exists(Resource $resource)
    {
        return isset($this->resources[$resource->getId()]);

    }
}
