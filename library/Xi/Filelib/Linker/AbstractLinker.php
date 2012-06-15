<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Linker;

use Xi\Filelib\Linker\Linker;
use Xi\Filelib\Configurator;

/**
 * An abstract linker class.
 *
 * @author pekkis
 */
abstract class AbstractLinker implements Linker
{
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
}
