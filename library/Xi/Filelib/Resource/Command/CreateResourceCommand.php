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

class CreateResourceCommand extends AbstractResourceCommand
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var string
     */
    private $path;

    public function __construct(Resource $resource, $path)
    {
        $this->resource = $resource;
        $this->path = $path;
    }

    public function execute()
    {
        $event = new ResourceEvent($this->resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_BEFORE_CREATE, $event);

        $this->backend->createResource($this->resource);
        $this->storage->store($this->resource, $this->path);

        $event = new ResourceEvent($this->resource);
        $this->eventDispatcher->dispatch(Events::RESOURCE_AFTER_CREATE, $event);

        return $this->resource;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.resource.create';
    }
}
