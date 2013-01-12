<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Serializable;
use Xi\Filelib\Queue\Enqueueable;

interface EnqueueableCommand extends Command, Enqueueable, Serializable
{
    const STRATEGY_SYNCHRONOUS = 'sync';
    const STRATEGY_ASYNCHRONOUS = 'async';

    public function execute();

}
