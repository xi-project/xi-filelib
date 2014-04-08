<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

/**
 * File finder
 */
class FileFinder extends AbstractFinder
{
    protected $fields = array(
        'id',
        'folder_id',
        'name',
        'uuid',
    );

    protected $resultClass = 'Xi\Filelib\File\File';
}
