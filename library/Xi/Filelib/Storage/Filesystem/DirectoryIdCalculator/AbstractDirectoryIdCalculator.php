<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Configurator;

abstract class AbstractDirectoryIdCalculator implements DirectoryIdCalculator
{
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
    
}
