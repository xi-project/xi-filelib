<?php

namespace Xi\Filelib\Tests\Queue\Processor;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\FileLibrary;

class TestCommand implements EnqueueableCommand
{
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

    public function attachTo(FileLibrary $filelib)
    {

    }


    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getOutput()
    {
        return new NullOutput();
    }

}
