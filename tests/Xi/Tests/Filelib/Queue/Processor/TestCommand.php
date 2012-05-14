<?php

namespace Xi\Tests\Filelib\Queue\Processor;

use Xi\Filelib\Command;

class TestCommand implements Command
{
    
    private $fileOperator;
    
    private $folderOperator;
    
    private $isExecuted = false;
    
    public function execute()
    {
        $this->isExecuted = true;
        return 'lus';
    }
    
    
    public function isExecuted()
    {
        return $this->isExecuted;
    }
    
}