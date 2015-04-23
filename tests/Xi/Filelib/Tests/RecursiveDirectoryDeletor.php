<?php

namespace Xi\Filelib\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RecursiveDirectoryDeletor
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = ROOT_TESTS . '/data/' . $dir;

        if (!is_dir($this->dir)) {
            throw new \Exception('Not a directory');
        }
    }

    public function delete()
    {
        $diter = new RecursiveDirectoryIterator($this->dir);
        $riter = new RecursiveIteratorIterator($diter, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($riter as $item) {
            if ($item->isFile() && $item->getFilename() !== '.gitignore') {
                @unlink($item->getPathName());
            }
        }

        foreach ($riter as $item) {
            if ($item->isDir() && !in_array($item->getPathName(), array('.', '..'))) {
                @rmdir($item->getPathName());
            }
        }
    }

}
