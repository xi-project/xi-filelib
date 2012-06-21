<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Transliterator;

interface Transliterator
{

    /**
     * Transliterates a given string
     *
     * @param string $str
     * @return string
     */
    public function transliterate($str);

}