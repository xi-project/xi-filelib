<?php

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
            unlink ($this->getPath());
        }
    }
}
