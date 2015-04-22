<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\ReversibleLinker;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\UniversalLeveledDirectoryIdCalculator;
use Xi\Filelib\Version;

/**
 * Universal sequential linker
 *
 * @author pekkis
 */
class UniversalSequentialLinker implements ReversibleLinker
{
    /**
     * @var DirectoryIdCalculator
     */
    private $directoryIdCalculator;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    public function __construct($directoryLevels = 3, $filesPerDirectory = 500)
    {
        $this->directoryIdCalculator = new UniversalLeveledDirectoryIdCalculator($directoryLevels, $filesPerDirectory);
    }

    /**
     * @param File $file
     * @return string
     */
    protected function getFileName(File $file)
    {
        return $file->getUuid();
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->fileRepository = $filelib->getFileRepository();
    }

    /**
     * @param string $link
     * @return array
     */
    public function reverseLink($link)
    {
        $pinfo = pathinfo($link);
        $split = explode('-', $pinfo['filename']);
        $version = array_pop($split);
        $version = Version::get($version);
        $uuid = implode('-', $split);
        $file = $this->fileRepository->findByUuid($uuid);
        return array($file, $version);
    }

    /**
     * Returns directory path for specified file id
     *
     * @param  File   $file
     * @return string
     */
    public function getDirectoryId(File $file)
    {
        return $this->directoryIdCalculator->calculateDirectoryId($file);
    }

    /**
     * Returns link for a version of a file
     *
     * @param  File   $file
     * @param  Version $version   Version identifier
     * @param  string $extension Extension
     * @return string Versioned link
     */
    public function getLink(File $file, Version $version, $extension)
    {

        $link = $this->getBaseLink($file);
        $pinfo = pathinfo($link);
        $link = $pinfo['dirname'] . '/' . $pinfo['filename'] . '-' . $version->toString();
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
}
