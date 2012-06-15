<?php

namespace Xi\Filelib\Linker;

use Xi\Filelib\Linker\AbstractLinker;
use Xi\Filelib\Linker\Linker;
use Xi\Filelib\File\File;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;

/**
 * Sequential linker creates a sequential link with n levels of directories with m files per directory
 *
 * @author pekkis
 * @author Petri Mahanen
 */
class SequentialLinker extends AbstractLinker implements Linker
{
    /**
     * @var integer Files per directory
     */
    private $filesPerDirectory = 500;

    /**
     * @var integer Levels in directory structure
     */
    private $directoryLevels = 1;

    /**
     * @var LeveledDirectoryIdCalculator
     */
    private $directoryIdCalculator;

    /**
     * Sets files per directory
     *
     * @param  integer          $filesPerDirectory
     * @return SequentialLinker
     */
    public function setFilesPerDirectory($filesPerDirectory)
    {
        $this->getDirectoryIdCalculator()->setFilesPerDirectory($filesPerDirectory);

        return $this;
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
     * Sets levels per directory hierarchy
     *
     * @param  integer          $directoryLevels
     * @return SequentialLinker
     */
    public function setDirectoryLevels($directoryLevels)
    {
        $this->getDirectoryIdCalculator()->setDirectoryLevels($directoryLevels);

        return $this;
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
    public function getDirectoryId($file)
    {
        return $this->getDirectoryIdCalculator()->calculateDirectoryId($file);
    }

    /**
     * Returns link for a version of a file
     *
     * @param  File   $file
     * @param  string $version   Version identifier
     * @param  string $extension Extension
     * @return string Versioned link
     */
    public function getLinkVersion(File $file, $version, $extension)
    {

        $link = $this->getLink($file);
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
    public function getLink(File $file)
    {
        $url = array();
        $url[] = $this->getDirectoryId($file);
        $name = $file->getName();
        $url[] = $name;
        $url = implode(DIRECTORY_SEPARATOR, $url);

        return $url;
    }

    /**
     * Returns directory id calculator
     *
     * @return LeveledDirectoryIdCalculator
     */
    private function getDirectoryIdCalculator()
    {
        if (!$this->directoryIdCalculator) {
            $this->directoryIdCalculator = new LeveledDirectoryIdCalculator();
            $this->directoryIdCalculator->setDirectoryLevels($this->getDirectoryLevels());
            $this->directoryIdCalculator->setFilesPerDirectory($this->getFilesPerDirectory());
        }

        return $this->directoryIdCalculator;
    }
}
