<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;

class NullCommand implements Command
{
    private $topic;

    private $returnValue;

    public function __construct($topic = 'xooxer', $returnValue = null)
    {
        $this->topic = $topic;
        $this->returnValue = null;
    }

    public function attachTo(FileLibrary $filelib)
    {}

    public function execute()
    {
        return $this->returnValue;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($data)
    {}
}

