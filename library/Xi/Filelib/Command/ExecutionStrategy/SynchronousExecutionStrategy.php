<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command\ExecutionStrategy;

use Xi\Filelib\Command\Command;

class SynchronousExecutionStrategy implements ExecutionStrategy
{
    /**
     * @param Command $command
     * @return mixed
     */
    public function execute(Command $command)
    {
        return $command->execute();
    }
}
