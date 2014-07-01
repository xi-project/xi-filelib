<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

/**
 * Reversable linker
 *
 * @author pekkis
 */
interface ReversibleLinker extends Linker
{
    /**
     * @param string $link
     * @return array A tuple of file and version
     */
    public function reverseLink($link);
}
