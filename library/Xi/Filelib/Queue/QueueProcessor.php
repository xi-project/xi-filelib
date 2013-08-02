<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use Symfony\Component\Console\Output\NullOutput;
use Xi\Filelib\Command;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Default implementation of a queue processor
 */
class QueueProcessor
{
    /**
     *
     * @var FileLibrary
     */
    protected $filelib;

    /**
     *
     * @var Queue
     */
    protected $queue;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(FileLibrary $filelib, OutputInterface $output = null)
    {
        $this->filelib = $filelib;
        $this->queue = $filelib->getQueue();
        $this->output = $output ?: new NullOutput();
    }

    /**
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    /**
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

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
            $this->output->writeln("Nothing to process");
            return false;
        }

        $this->output->writeln("Processing a message");

        $command = $this->extractCommandFromMessage($message);

        $filelib = $this->filelib;
        $output = $this->output;

        $this->processMessage($message, function (QueueProcessor $processor) use ($command, $filelib, $output) {

            $class = get_class($command);
            $output->writeln("Processing a command of class '{$class}'");

            $command->attachTo($filelib);
            $command->setOutput($output);
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
