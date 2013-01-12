<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Xi\Filelib\Folder\Folder;

/**
 * Folder event
 */
class FolderEvent extends IdentifiableEvent
{
    public function __construct(Folder $folder)
    {
        parent::__construct($folder);
    }

    /**
     * Returns folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->getIdentifiable();
    }
}
