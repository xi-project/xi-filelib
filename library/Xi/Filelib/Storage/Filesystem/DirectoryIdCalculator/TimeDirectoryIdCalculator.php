<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use DateTime;
use Xi\Filelib\FilelibException;

class TimeDirectoryIdCalculator extends AbstractDirectoryIdCalculator
{
    /**
     * @var string
     */
    private $format = 'Y/m/d';

    /**
     * Sets directory creation format
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Returns directory creation format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @see DirectoryIdCalculator::calculateDirectoryId
     */
    public function calculateDirectoryId($resource)
    {
        $dt = $resource->getDateCreated();

        if(!($dt instanceof DateTime)) {
            throw new FilelibException("Upload date not set in file");
        }

        $path = $dt->format($this->getFormat());

        return $path;
    }
}
