<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Default implementation of a queue processor
 */
class QueueProcessor
{
    /**
     *
     * @var Queue
     */
    protected $queue;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var MessageHandler[]
     */
    protected $handlers = [];

    public function __construct(Queue $queue, OutputInterface $output = null)
    {
        $this->queue = $queue;
        $this->output = $output ?: new NullOutput();
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function registerHandler(MessageHandler $handler)
    {
        array_unshift($this->handlers, $handler);
    }

    /**
     * Processes a single message from the queue
     *
     * @return boolean False if there are no messages in the queue.
     */
    public function process()
    {
        $message = $this->queue->dequeue();

        if (!$message) {
            $this->output->writeln("Nothing to process");
            return false;
        }

        $this->output->writeln(
            sprintf("Received a message of type '%s'", $message->getType())
        );

        $result = $this->handleMessage($message);
        if (!$result) {
            throw new \RuntimeException(sprintf("No handler will handle a message of type '%s'", $message->getType()));
        }

        if ($result->isSuccess()) {
            $this->queue->ack($message);

            foreach ($result->getMessages() as $message) {
                $this->queue->enqueue($message);
            }
        }

        return true;
    }

    private function handleMessage(Message $message)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->willHandle($message)) {
                return $handler->handle($message);
            }
        }
        return false;
    }
}
