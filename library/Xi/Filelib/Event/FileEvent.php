<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Xi\Filelib\File\File;

/**
 * File event
 */
class FileEvent extends IdentifiableEvent
{
    public function __construct(File $file)
    {
        parent::__construct($file);
    }

    /**
     * Returns file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->getIdentifiable();
    }
}
