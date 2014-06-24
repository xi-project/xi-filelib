<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

class Retrieved
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $isTemporary;

    /**
     * @param string $path
     * @param bool $isTemporary
     */
    public function __construct($path, $isTemporary)
    {
        $this->path = $path;
        $this->isTemporary = $isTemporary;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->isTemporary;
    }

    public function __destruct()
    {
        if ($this->isTemporary()) {
            unlink($this->getPath());
        }
    }
}
