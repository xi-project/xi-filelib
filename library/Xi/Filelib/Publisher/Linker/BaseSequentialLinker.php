<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Publisher\Linker;

/**
 * Base class for sequential linkers
 *
 * @author pekkis
 * @author Petri Mahanen
 */
abstract class BaseSequentialLinker
{
    /**
     * @var integer Files per directory
     */
    private $filesPerDirectory;

    /**
     * @var integer Levels in directory structure
     */
    private $directoryLevels;


    public function __construct($directoryLevels = 3, $filesPerDirectory = 500)
    {
        $this->directoryLevels = $directoryLevels;
        $this->filesPerDirectory = $filesPerDirectory;
    }


    /**
     * Returns files per directory
     *
     * @return integer
     */
    public function getFilesPerDirectory()
    {
        return $this->filesPerDirectory;
    }

    /**
     * Returns levels in directory hierarchy
     *
     * @return integer
     */
    public function getDirectoryLevels()
    {
        return $this->directoryLevels;
    }

    /**
     * Returns directory path for specified file id
     *
     * @param  File   $file
     * @return string
     */
    public function getDirectoryId(File $file)
    {
        return $this->calculateDirectoryId($file);
    }

    /**
     * Returns link for a version of a file
     *
     * @param  File   $file
     * @param  string $version   Version identifier
     * @param  string $extension Extension
     * @return string Versioned link
     */
    public function getLink(File $file, $version, $extension)
    {

        $link = $this->getBaseLink($file);
        $pinfo = pathinfo($link);
        $link = $pinfo['dirname'] . '/' . $pinfo['filename'] . '-' . $version;
        $link .= '.' . $extension;

        return $link;
    }

    /**
     * Returns a link for a file
     *
     * @param  File   $file
     * @return string Link
     */
    protected function getBaseLink(File $file)
    {
        $url = array();
        $url[] = $this->getDirectoryId($file);
        $name = $this->getFileName($file);
        $url[] = $name;
        $url = implode(DIRECTORY_SEPARATOR, $url);

        return $url;
    }

    private function calculateDirectoryId(File $file)
    {
        if (!is_numeric($file->getId())) {
            throw new InvalidArgumentException(
                "Leveled linker requires numeric file ids ('{$file->getId()}' was provided)"
            );
        }

        if ($this->getDirectoryLevels() < 1) {
            throw new InvalidArgumentException("Invalid number of directory levels ({$this->getDirectoryLevels()})");
        }

        $fileId = $file->getId();

        $directoryLevels = $this->getDirectoryLevels() + 1;
        $filesPerDirectory = $this->getFilesPerDirectory();

        $arr = array();
        $tmpfileid = $fileId - 1;

        for ($count = 1; $count <= $directoryLevels; ++$count) {
            $lus = $tmpfileid / pow($filesPerDirectory, $directoryLevels - $count);
            $tmpfileid = $tmpfileid % pow($filesPerDirectory, $directoryLevels - $count);
            $arr[] = floor($lus) + 1;
        }

        $puuppa = array_pop($arr);

        return implode(DIRECTORY_SEPARATOR, $arr);

    }

    /**
     * @param File $file
     * @return string
     */
    abstract protected function getFileName(File $file);
}
