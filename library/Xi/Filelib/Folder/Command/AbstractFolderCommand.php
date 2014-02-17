<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Command\Command;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\FileLibrary;

abstract class AbstractFolderCommand implements Command
{
    /**
     *
     * @var FolderOperator
     */
    protected $folderOperator;

    public function attachTo(FileLibrary $filelib)
    {
        $this->folderOperator = $filelib->getFolderOperator();
    }
}
