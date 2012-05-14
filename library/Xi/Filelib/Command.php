<?php

namespace Xi\Filelib;

interface Command
{

    const STRATEGY_SYNCHRONOUS = 'sync';
    const STRATEGY_ASYNCHRONOUS = 'async';
    
    public function execute();
    
}
