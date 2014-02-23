<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Queue\UuidReceiver;

class NullCommand implements Command, UuidReceiver
{
    private $topic;

    private $returnValue;

    private $uuid;

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

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function unserialize($data)
    {}
}

