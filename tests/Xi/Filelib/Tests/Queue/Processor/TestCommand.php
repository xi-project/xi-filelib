<?php

namespace Xi\Filelib\Tests\Queue\Processor;

use Xi\Filelib\EnqueueableCommand;

class TestCommand implements EnqueueableCommand
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
