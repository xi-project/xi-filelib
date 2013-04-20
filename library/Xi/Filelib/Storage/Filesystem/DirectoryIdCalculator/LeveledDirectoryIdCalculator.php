<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\FilelibException;

/**
 * Creates directories in a leveled hierarchy based on a numeric file id
 *
 */
class LeveledDirectoryIdCalculator extends AbstractDirectoryIdCalculator
{
    public function __construct($directoryLevels = 3, $filesPerDirectory = 500)
    {
        $this->directoryLevels = $directoryLevels;
        $this->filesPerDirectory = $filesPerDirectory;
    }

    /**
     * @var integer Files per directory
     */
    private $filesPerDirectory;

    /**
     * @var integer Levels in directory structure
     */
    private $directoryLevels;

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
     * @see DirectoryIdCalculator::calculateDirectoryId
     */
    public function calculateDirectoryId($resource)
    {
        if (!is_numeric($resource->getId())) {
            throw new FilelibException("Leveled directory id calculator requires numeric file ids ('{$resource->getId()}' was provided)");
        }

        if ($this->getDirectoryLevels() < 1) {
            throw new FilelibException("Invalid number of directory levels ('{$this->getDirectoryLevels()}')");
        }

        $resourceId = $resource->getId();

        $directoryLevels = $this->getDirectoryLevels() + 1;
        $filesPerDirectory = $this->getFilesPerDirectory();

        $arr = array();
        $tmpfileid = $resourceId - 1;

        for ($count = 1; $count <= $directoryLevels; ++$count) {
            $lus = $tmpfileid / pow($filesPerDirectory, $directoryLevels - $count);
            $tmpfileid = $tmpfileid % pow($filesPerDirectory, $directoryLevels - $count);
            $arr[] = floor($lus) + 1;
        }

        $puuppa = array_pop($arr);

        return implode(DIRECTORY_SEPARATOR, $arr);

    }
}
