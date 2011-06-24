<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

abstract class AbstractDirectoryIdCalculator implements DirectoryIdCalculator
{
    public function __construct($options = array())
    {
        \Xi\Filelib\Options::setConstructorOptions($this, $options);
    }
    
}
