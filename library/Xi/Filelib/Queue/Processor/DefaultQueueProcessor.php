<?php

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\FileLibrary\File\FileOperator;
use Xi\Filelib\FileLibrary\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Queue\Message;
use Xi\Filelib\Command;
use ReflectionObject;
use InvalidArgumentException;
use Xi\Filelib\FilelibException;

/**
 * Default implementation of a queue processor
 */
class DefaultQueueProcessor extends AbstractQueueProcessor
{
    /**
     * Processes a single message from the queue
     *
     * @return mixed
     */
    public function process()
    {
        $queue = $this->getQueue();

        $message = $queue->dequeue();

        if (!$message) {
            return null;
        }

        $command = $this->extractCommandFromMessage($message);

        return $this->tryToProcess($message, function(DefaultQueueProcessor $processor) use ($command) {
            $processor->injectOperators($command);
            return $command->execute();
        });
    }

    /**
     * Tries to process a message with a processor function
     *
     * @param Message $message
     * @param callable $processorFunction
     * @return boolean Success or not
     */
    public function tryToProcess(Message $message, $processorFunction)
    {
        try {
            $ret = $processorFunction($this);
            $this->getQueue()->ack($message);

            if ($ret instanceof Command) {
                $this->getQueue()->enqueue($ret);
            }

            return true;

        } catch (FilelibException $e) {
            return false;
        }
    }

    /**
     * Extracts a command from a message
     *
     * @param Message $message
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

