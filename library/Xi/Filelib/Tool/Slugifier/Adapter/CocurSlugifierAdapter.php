<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier\Adapter;

use Cocur\Slugify\Slugify;

class CocurSlugifierAdapter implements SlugifierAdapter
{
    /**
     * @var Slugify
     */
    private $slugifier;

    public function __construct()
    {
        $this->slugifier = Slugify::create();
    }

    /**
     * @param string $unslugged
     * @return string
     */
    public function slugify($unslugged)
    {
        return $this->slugifier->slugify($unslugged);
    }
}
