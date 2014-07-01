<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Command\Commander;
use Xi\Filelib\Command\CommanderClient;

abstract class AbstractRepository implements Attacher, CommanderClient
{
    /**
     * @var Commander
     */
    protected $commander;

    /**
     * @var Backend
     */
    protected $backend;

    public function attachTo(FileLibrary $filelib)
    {
        $this->backend = $filelib->getBackend();
        $this->commander = $filelib->getCommander();
        $this->commander->addClient($this);
    }

    public function getExecutionStrategy($command)
    {
        return $this->commander->getExecutionStrategy($command);
    }

    public function setExecutionStrategy($command, $strategy)
    {
        $this->commander->setExecutionStrategy($command, $strategy);
        return $this;
    }

    public function createCommand($commandClass, array $args = array())
    {
        return $this->commander->createCommand($commandClass, $args);
    }

    public function createExecutable($commandClass, array $args = array())
    {
        return $this->commander->createExecutable($commandClass, $args);
    }
}
