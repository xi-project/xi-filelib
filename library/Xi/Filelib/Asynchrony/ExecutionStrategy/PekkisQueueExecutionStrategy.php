<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\ExecutionStrategy;

use Xi\Filelib\Asynchrony\Serializer\DataSerializer\RepositorySerializer;
use Xi\Filelib\LogicException;
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
     * @var EventDispatchingQueue
     */
    private $queue;

    private $attached = false;

    /**
     * @param EventDispatchingQueue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        $this->attached = false;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $serializer = new AsynchronyDataSerializer();
        $serializer->attachTo($filelib);

        $repositorySerializer = new RepositorySerializer();
        $repositorySerializer->attachTo($filelib);
        $serializer->addSerializer($repositorySerializer);

        $this->queue->addDataSerializer(
            $serializer
        );

        $this->attached = true;
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
        if (!$this->attached) {
            throw new LogicException('Must be attached to a file library');
        }

        $command = new SerializedCallback(
            $callback,
            $params
        );

        return $this->queue->enqueue('xi_filelib.asynchrony.command', $command);
    }
}
