<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Pekkis\Queue\QueueInterface;
use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Command\ExecutionStrategy\AsynchronousExecutionStrategy;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\Command\ExecutionStrategy\SynchronousExecutionStrategy;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\RuntimeException;

class Commander
{
    /**
     * @var EventDispatchingQueue
     */
    private $queue;

    /**
     * @var array
     */
    private $commandDefinitions = array();

    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @param FileLibrary $filelib
     */
    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * @param CommanderClient $client
     * @return Commander
     */
    public function addClient(CommanderClient $client)
    {
        foreach ($client->getCommandDefinitions() as $definition) {
            $this->addCommandDefinition($definition);
        }

        return $this;
    }


    /**
     * @param QueueInterface $queue
     * @return Commander
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @param string $command
     * @return string
     */
    public function getExecutionStrategy($command)
    {
        return $this->getCommandDefinition($command)->getStrategy();
    }

    /**
     * @param string $command
     * @param string $strategy
     * @return Commander
     */
    public function setExecutionStrategy($command, $strategy)
    {
        if (!$this->isExecutionStrategyAvailable($strategy)) {
            throw new RuntimeException(
                sprintf("Execution strategy '%s' not available", $strategy)
            );
        }
        $this->getCommandDefinition($command)->setStrategy($strategy);
        return $this;
    }

    /**
     * @param string $command
     * @param array $args
     * @return Command
     */
    public function createCommand($command, array $args = array())
    {
        $commandDefinition = $this->getCommandDefinition($command);
        $reflClass = new \ReflectionClass($commandDefinition->getClass());

        /** @var Command $command */
        $command = $reflClass->newInstanceArgs($args);
        $command->attachTo($this->filelib);
        return $command;
    }

    /**
     * @param string $command
     * @param array $args
     * @return Executable
     */
    public function createExecutable($command, array $args = array())
    {
        return new Executable(
            $this->createCommand($command, $args),
            $this->createExecutionStrategy($this->getCommandDefinition($command)->getStrategy())
        );
    }

    /**
     * @param string $strategy
     * @return ExecutionStrategy
     */
    private function createExecutionStrategy($strategy)
    {
        switch ($strategy) {
            case ExecutionStrategy::STRATEGY_SYNCHRONOUS:
                $strategy = new SynchronousExecutionStrategy();
                break;
            case ExecutionStrategy::STRATEGY_ASYNCHRONOUS:
                $strategy = new AsynchronousExecutionStrategy($this->queue);
                break;
        }
        return $strategy;
    }

    /**
     * @param CommandDefinition $commandDefinition
     * @return $this
     * @throws RuntimeException
     */
    public function addCommandDefinition(CommandDefinition $commandDefinition)
    {
        if (!$this->isExecutionStrategyAvailable($commandDefinition->getStrategy())) {
            throw new RuntimeException(
                sprintf("Execution strategy '%s' not available", $commandDefinition->getStrategy())
            );
        }
        $this->commandDefinitions[$commandDefinition->getClass()] = $commandDefinition;
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableExecutionStrategies()
    {
        $strategies = array(ExecutionStrategy::STRATEGY_SYNCHRONOUS);
        if ($this->queue) {
            $strategies[] = ExecutionStrategy::STRATEGY_ASYNCHRONOUS;
        }
        return $strategies;
    }

    /**
     * @param string $executionStrategy
     * @return bool
     */
    public function isExecutionStrategyAvailable($executionStrategy)
    {
        return in_array($executionStrategy, $this->getAvailableExecutionStrategies());
    }

    /**
     * @param string $name
     * @return CommandDefinition
     * @throws InvalidArgumentException
     */
    private function getCommandDefinition($command)
    {
        if (!isset($this->commandDefinitions[$command])) {
            throw new InvalidArgumentException(
                sprintf("Command definition '%s' not found", $command)
            );
        }

        return $this->commandDefinitions[$command];
    }
}
