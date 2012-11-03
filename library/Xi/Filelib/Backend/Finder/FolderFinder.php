<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

/**
 * Folder finder
 */
class FolderFinder extends AbstractFinder
{
    protected $fields = array(
        'id',
        'parent_id',
    );

    protected $resultClass = 'Xi\Filelib\Folder\Folder';
}
