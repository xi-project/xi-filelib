<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Configurator;

/**
 * Abstract convenience publisher base class implementing common methods
 *
 * @author pekkis
 *
 */
abstract class AbstractPublisher implements Publisher
{
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }


    /**
     * @var FileLibrary Filelib
     */
    private $filelib;

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
