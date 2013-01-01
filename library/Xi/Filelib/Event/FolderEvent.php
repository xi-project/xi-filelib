<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Folder\Folder;

class FolderEvent extends Event
{
    /**
     * @var Folder
     */
    private $folder;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Returns folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

}
