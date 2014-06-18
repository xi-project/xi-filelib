<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Xi\Filelib\File\File;

class PublisherEvent extends FileEvent
{
    /**
     * @var array
     */
    private $versions;

    public function __construct(File $file, array $versions)
    {
        parent::__construct($file);
        $this->versions = $versions;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }
}
