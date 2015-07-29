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
}
