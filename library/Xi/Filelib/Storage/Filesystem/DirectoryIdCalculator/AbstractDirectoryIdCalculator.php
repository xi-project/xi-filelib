<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Configurator;

abstract class AbstractDirectoryIdCalculator implements DirectoryIdCalculator
{
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
}
