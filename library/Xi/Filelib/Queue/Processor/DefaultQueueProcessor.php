<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\Queue\Message;
use Xi\Filelib\Command;
use InvalidArgumentException;

/**
 * Default implementation of a queue processor
 */
class DefaultQueueProcessor extends AbstractQueueProcessor
{
    /**
     * Processes a single message from the queue
     *
     * @return boolean False if there are no messages in the queue.
     */
    public function process()
    {
        $queue = $this->getQueue();

        $message = $queue->dequeue();

        if (!$message) {
            return false;
        }

        $command = $this->extractCommandFromMessage($message);

        $filelib = $this->filelib;
        $this->processMessage($message, function (DefaultQueueProcessor $processor) use ($command, $filelib) {
            $command->attachTo($filelib);
            $command->execute();
        });

        return true;
    }

    /**
     * Processes a message with a processor function
     *
     * Any exceptions thrown here are left for the caller to handle.
     *
     * TODO: Messages should be acked (and maybe logged?) when an exception is
     *       thrown.
     *
     * @param Message  $message
     * @param callable $processorFunction
     */
    public function processMessage(Message $message, $processorFunction)
    {
        $ret = $processorFunction($this);

        $this->getQueue()->ack($message);

        if ($ret instanceof Command) {
            $this->getQueue()->enqueue($ret);
        }
    }

    /**
     * Extracts a command from a message
     *
     * @param  Message                  $message
     * @return Command
     * @throws InvalidArgumentException
     */
    public function extractCommandFromMessage(Message $message)
    {
        $command = unserialize($message->getBody());
        if (!$command instanceof Command) {
            throw new InvalidArgumentException("Queue processor expects commands wrapped in a message");
        }


        return $command;
    }
}
