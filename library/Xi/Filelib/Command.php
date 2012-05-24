<?php

namespace Xi\Filelib;

use Xi\Filelib\Queue\Enqueueable;

interface Command extends Enqueueable
{

    const STRATEGY_SYNCHRONOUS = 'sync';
    const STRATEGY_ASYNCHRONOUS = 'async';

    public function execute();

}
