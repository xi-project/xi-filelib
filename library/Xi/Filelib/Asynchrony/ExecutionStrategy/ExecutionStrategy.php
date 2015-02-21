<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\ExecutionStrategy;

use Xi\Filelib\Command\Command;

interface ExecutionStrategy
{
    public function getIdentifier();

    public function execute(callable $callback, $params = []);
}
