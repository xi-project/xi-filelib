<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier;

/**
 * Slugifier interface
 */
interface Slugifier
{

    /**
     * Slugifies a path
     *
     * @param  string $path
     * @return string
     */
    public function slugifyPath($path);

    /**
     * Slugifies a word
     *
     * @param  string $unslugged
     * @return string
     */
    public function slugify($unslugged);

}
