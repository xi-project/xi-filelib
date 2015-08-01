<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use Xi\Filelib\Resource\ConcreteResource;
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
     * @param ConcreteResource $resource
     * @param string $tempFile
     */
    public function store(ConcreteResource $resource, $tempFile)
    {
        $this->resources[$resource->getId()] = $tempFile;
    }

    public function retrieve(ConcreteResource $resource)
    {
        return new Retrieved($this->resources[$resource->getId()], false);
    }

    public function delete(ConcreteResource $resource)
    {
        unset($this->resources[$resource->getId()]);
    }

    public function exists(ConcreteResource $resource)
    {
        return isset($this->resources[$resource->getId()]);

    }
}
