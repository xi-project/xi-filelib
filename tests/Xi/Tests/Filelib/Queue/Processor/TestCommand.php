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

    public function getEnqueueReturnValue()
    {
        return 'tussihovi';
    }

    public function serialize()
    {
        return serialize($this->isExecuted());
    }

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->isExecuted = $unserialized;
    }

}