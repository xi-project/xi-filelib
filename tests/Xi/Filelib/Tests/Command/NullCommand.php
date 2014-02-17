<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\Command;
use Xi\Filelib\FileLibrary;

class NullCommand implements Command
{
    public function attachTo(FileLibrary $filelib)
    {}

    public function execute()
    {}

    public function getTopic()
    {
        return 'xooxer';
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($data)
    {}
}

