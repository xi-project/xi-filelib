<?php

namespace Xi\Filelib\Plugin\Image\Command;

use \Imagick;

/**
 * Abstract convenience class for versionplugin plugins
 * 
 * @author pekkis
 *
 */
abstract class AbstractCommand implements Command
{
    
    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
}