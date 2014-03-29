<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier\Adapter;

/**
 * Slugifier interface
 */
interface SlugifierAdapter
{
    /**
     * Slugifies a word
     *
     * @param  string $unslugged
     * @return string
     */
    public function slugify($unslugged);
}
