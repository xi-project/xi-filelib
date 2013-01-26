<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\Command;
use Xi\Filelib\Tool\UuidGenerator\UuidGenerator;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract convenience class for operators
 *
 * @author pekkis
 *
 */
abstract class AbstractOperator
{
    /**
     * Filelib reference
     *
     * @var FileLibrary
     */
    protected $filelib;

    /**
     * Commands and their default strategies
     *
     * @var array
     */
    protected $commandStrategies = array();

    /**
     *
     * @var UuidGenerator
     */
    protected $uuidGenerator;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->getFilelib()->getBackend();
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->getFilelib()->getStorage();
    }

    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->getFilelib()->getPublisher();
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    /**
     * Returns Acl
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->getFilelib()->getAcl();
    }

    /**
     * Returns Event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getFilelib()->getEventDispatcher();
    }

    /**
     * Returns queue
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->getFilelib()->getQueue();
    }

    private function assertCommandExists($command)
    {
        if (!isset($this->commandStrategies[$command])) {
            throw new \InvalidArgumentException("Command '{$command}' is not supported");
        }
    }

    private function assertStrategyExists($strategy)
    {
        if (!in_array($strategy, array(EnqueueableCommand::STRATEGY_ASYNCHRONOUS, EnqueueableCommand::STRATEGY_SYNCHRONOUS))) {
            throw new \InvalidArgumentException("Invalid command strategy '{$strategy}'");
        }
    }

    public function getCommandStrategy($command)
    {
        $this->assertCommandExists($command);

        return $this->commandStrategies[$command];
    }

    public function setCommandStrategy($command, $strategy)
    {
        $this->assertCommandExists($command);
        $this->assertStrategyExists($strategy);
        $this->commandStrategies[$command] = $strategy;

        return $this;
    }

    public function createCommand($commandClass, array $args = array())
    {
        $reflClass = new \ReflectionClass($commandClass);

        return $reflClass->newInstanceArgs($args);
    }

    public function executeOrQueue(EnqueueableCommand $commandObj, $commandName, array $callbacks = array())
    {
        $strategy = $this->getCommandStrategy($commandName);
        if ($strategy == EnqueueableCommand::STRATEGY_ASYNCHRONOUS) {
            $ret = $this->getQueue()->enqueue($commandObj);
        } else {
            $ret = $commandObj->execute();
        }

        return $this->executeOrQueueHandleCallbacks($strategy, $callbacks, $ret);
    }

    private function executeOrQueueHandleCallbacks($strategy, $callbacks, $ret)
    {
        if (isset($callbacks[$strategy])) {
            return $callbacks[$strategy]($this, $ret);
        }

        return $ret;
    }

    /**
     * Generates UUID
     *
     * @return string
     */
    public function generateUuid()
    {
        return $this->getUuidGenerator()->v4();
    }

    /**
     * @return UuidGenerator
     */
    protected function getUuidGenerator()
    {
        if (!$this->uuidGenerator) {
            $this->uuidGenerator = new PHPUuidGenerator();
        }

        return $this->uuidGenerator;
    }
}
