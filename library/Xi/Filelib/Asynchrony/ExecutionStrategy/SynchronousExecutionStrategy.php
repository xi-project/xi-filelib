<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\ExecutionStrategy;

use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;

class SynchronousExecutionStrategy implements ExecutionStrategy
{
    /**
     * @return string
     */
    public function getIdentifier()
    {
        return ExecutionStrategies::STRATEGY_SYNC;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {

    }

    /**
     * @param callable $callback
     * @param array $params
     * @return mixed
     */
    public function execute(callable $callback, $params = [])
    {
        return call_user_func_array($callback, $params);
    }
}
