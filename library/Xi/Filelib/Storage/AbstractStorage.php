<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Configurator;
use Xi\Filelib\Storage\Storage;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class AbstractStorage implements Storage
{
    /**
     * @param  array           $options
     * @return AbstractStorage
     */
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
}
