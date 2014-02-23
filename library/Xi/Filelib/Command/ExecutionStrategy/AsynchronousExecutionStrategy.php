<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command\ExecutionStrategy;

use Pekkis\Queue\Message;
use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Command\Command;

class AsynchronousExecutionStrategy implements ExecutionStrategy
{
    /**
     * @var EventDispatchingQueue
     */
    private $queue;

    /**
     * @param EventDispatchingQueue $queue
     */
    public function __construct(EventDispatchingQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param Command $command
     * @return Message
     */
    public function execute(Command $command)
    {
        return $this->queue->enqueue($command->getTopic(), $command);
    }
}
