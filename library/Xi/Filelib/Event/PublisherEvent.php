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

class PublisherEvent extends Event
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var array
     */
    private $versions;

    public function __construct(File $file, array $versions)
    {
        $this->file = $file;
        $this->versions = $versions;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }
}
