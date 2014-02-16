<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Attacher;
use Symfony\Component\Console\Output\OutputInterface;
use Serializable;

interface Command extends Attacher, Serializable
{
    const STRATEGY_SYNCHRONOUS = 'sync';
    const STRATEGY_ASYNCHRONOUS = 'async';

    public function execute();

    /**
     * @return string
     */
    public function getTopic();

}
