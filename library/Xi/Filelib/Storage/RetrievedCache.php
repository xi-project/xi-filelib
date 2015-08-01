<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

class RetrievedCache
{
    private $retrieved;

    private $retrievedVersions;

    /**
     * @param ConcreteResource $resource
     * @return Retrieved
     */
    public function get(ConcreteResource $resource)
    {
        return (isset($this->retrieved[$resource->getId()])) ? $this->retrieved[$resource->getId()] : false;
    }

    public function set(ConcreteResource $resource, Retrieved $retrieved)
    {
        $this->retrieved[$resource->getId()] = $retrieved;
    }

    public function delete(ConcreteResource $resource)
    {
        if (isset($this->retrieved[$resource->getId()])) {
            unset($this->retrieved[$resource->getId()]);
        }
    }
}
