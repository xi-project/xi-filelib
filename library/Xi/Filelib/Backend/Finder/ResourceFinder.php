<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

/**
 * Resource finder
 */
class ResourceFinder extends AbstractFinder
{
    protected $fields = array(
        'id',
        'hash',
    );

    protected $resultClass = 'Xi\Filelib\Resource\Resource';
}
