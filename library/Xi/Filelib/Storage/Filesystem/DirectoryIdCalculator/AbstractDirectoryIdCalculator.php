<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

abstract class AbstractDirectoryIdCalculator implements DirectoryIdCalculator
{
    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
}
