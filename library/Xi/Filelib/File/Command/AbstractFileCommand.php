<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\AbstractCommand;
use Xi\Filelib\FileLibrary;

abstract class AbstractFileCommand extends AbstractCommand implements FileCommand
{
    /**
     * @var FileOperator
     */
    protected $fileOperator;

    public function attachTo(FileLibrary $filelib)
    {
        $this->fileOperator = $filelib->getFileOperator();
    }
}
