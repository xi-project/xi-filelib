<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\ExecutionStrategy;

use Xi\Filelib\LogicException;
use Pekkis\Queue\Adapter\Adapter;
use Pekkis\Queue\Message;
use Pekkis\Queue\Queue;
use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Asynchrony\Serializer\AsynchronyDataSerializer;
use Xi\Filelib\Asynchrony\Serializer\SerializedCallback;
use Xi\Filelib\FileLibrary;

class PekkisQueueExecutionStrategy implements ExecutionStrategy
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var EventDispatchingQueue
     */
    private $queue;

    /**
     * @param EventDispatchingQueue $queue
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $serializer = new AsynchronyDataSerializer();
        $serializer->attachTo($filelib);

        $queue = new Queue($this->adapter);
        $queue->addDataSerializer(
            $serializer
        );

        $this->queue = new EventDispatchingQueue(
            $queue,
            $filelib->getEventDispatcher()
        );
    }

    /**
     * @return EventDispatchingQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }


    public function getIdentifier()
    {
        return ExecutionStrategies::STRATEGY_ASYNC_PEKKIS_QUEUE;
    }

    /**
     * @param callable $callback
     * @param array $params
     * @return Message
     */
    public function execute(callable $callback, $params = [])
    {
        if (!$this->queue) {
            throw new LogicException('Must be attached to a file library');
        }

        $command = new SerializedCallback(
            $callback,
            $params
        );

        return $this->queue->enqueue('xi_filelib.asynchrony.command', $command);
    }
}
