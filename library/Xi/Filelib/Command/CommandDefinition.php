<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

/**
 * Command definition
 */
class CommandDefinition
{
    private $class;

    private $strategy;

    /**
     * @param string $name
     * @param string $class
     * @param string $strategy
     */
    public function __construct($class, $strategy = ExecutionStrategy::STRATEGY_SYNCHRONOUS)
    {
        $this->class = $class;
        $this->setStrategy($strategy);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $strategy
     */
    public function setStrategy($strategy)
    {
        if (!in_array(
            $strategy,
            array(ExecutionStrategy::STRATEGY_ASYNCHRONOUS, ExecutionStrategy::STRATEGY_SYNCHRONOUS)
        )) {
            throw new \InvalidArgumentException("Invalid execution strategy '{$strategy}'");
        }

        $this->strategy = $strategy;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
