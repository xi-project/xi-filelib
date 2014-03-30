<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource\Command;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Events;
use Pekkis\Queue\Message;

class DeleteResourceCommand extends AbstractResourceCommand
{
    /**
     * @var Resource
     */
    private $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public function execute()
    {
        $event = new ResourceEvent($this->resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_DELETE, $event);

        $this->backend->deleteResource($this->resource);
        $this->storage->delete($this->resource);

        $event = new ResourceEvent($this->resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_DELETE, $event);

        return $this->resource;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.resource.delete';
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->resource = $data['resource'];
    }

    public function serialize()
    {
        return serialize(
            array(
                'resource' => $this->resource,
            )
        );
    }
}
