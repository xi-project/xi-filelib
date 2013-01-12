<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\FileProfile;

/**
 * File profile event
 */
class FileProfileEvent extends Event
{
    /**
     * @var FileProfile
     */
    private $profile;

    public function __construct(FileProfile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Returns plugin
     *
     * @return FileProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }
}
