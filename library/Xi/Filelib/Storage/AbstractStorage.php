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
use Xi\Filelib\FileLibrary;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class AbstractStorage implements Storage
{
    /**
     * @var FileLibrary Filelib
     */
    private $filelib;

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * Sets filelib
     *
     * @param FileLibrary $filelib
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }
}
