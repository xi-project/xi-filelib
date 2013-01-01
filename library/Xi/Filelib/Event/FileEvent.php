<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\File;

class FileEvent extends Event implements IdentifiableEvent
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Returns file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return File
     */
    public function getIdentifiable()
    {
        return $this->getFile();
    }
}
