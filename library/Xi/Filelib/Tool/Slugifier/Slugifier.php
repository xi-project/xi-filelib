<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\Slugifier;
use Xi\Filelib\Tool\Slugifier\Adapter\CocurSlugifierAdapter;
use Xi\Filelib\Tool\Slugifier\Adapter\SlugifierAdapter;

/**
 * Slugifier interface
 */
class Slugifier
{
    /**
     * @var SlugifierAdapter
     */
    private $adapter;

    /**
     * @param SlugifierAdapter $adapter
     */
    public function __construct(SlugifierAdapter $adapter = null)
    {
        if (!$adapter) {
            $adapter = new CocurSlugifierAdapter();
        }

        $this->adapter = $adapter;
    }

    /**
     * @param string $path
     * @return string
     */
    public function slugifyPath($path)
    {
        $path = explode('/', $path);

        $ret = array();
        foreach ($path as $fragment) {
            $ret[] = $this->slugify($fragment);
        }

        return implode('/', $ret);

    }

    /**
     * Slugifies a word
     *
     * @param  string $unslugged
     * @return string
     */
    public function slugify($unslugged)
    {
        return $this->adapter->slugify($unslugged);
    }
}
