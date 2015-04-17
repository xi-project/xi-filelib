<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Backend\Backend;

abstract class AbstractRepository implements Attacher
{
    /**
     * @var Backend
     */
    protected $backend;

    public function attachTo(FileLibrary $filelib)
    {
        $this->backend = $filelib->getBackend();
    }
}
