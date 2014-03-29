<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Xi\Filelib\Resource\Resource;

/**
 * Resource event
 */
class ResourceEvent extends IdentifiableEvent
{
    public function __construct(Resource $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Returns Resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->getIdentifiable();
    }
}
