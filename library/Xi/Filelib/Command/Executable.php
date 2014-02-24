<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class Executable
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var ExecutionStrategy
     */
    private $strategy;

    public function __construct(Command $command, ExecutionStrategy $strategy)
    {
        $this->command = $command;
        $this->strategy = $strategy;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->strategy->execute($this->command);
    }

    /**
     * @return ExecutionStrategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return Command
     */
    public function getCommand()
    {
        return $this->command;
    }
}
