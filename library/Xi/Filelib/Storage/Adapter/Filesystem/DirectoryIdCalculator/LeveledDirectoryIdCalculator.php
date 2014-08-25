<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\LogicException;
use Xi\Filelib\Versionable;

/**
 * Creates directories in a leveled hierarchy based on a numeric file id
 *
 */
class LeveledDirectoryIdCalculator implements DirectoryIdCalculator
{
    public function __construct($directoryLevels = 3, $filesPerDirectory = 500)
    {
        if ($directoryLevels < 1) {
            throw new InvalidArgumentException("Invalid number of directory levels ('{$directoryLevels}')");
        }

        if ($filesPerDirectory < 1) {
            throw new InvalidArgumentException("Invalid number of files per directory {$filesPerDirectory}')");
        }

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
    public function calculateDirectoryId(Versionable $obj)
    {
        if (!is_numeric($obj->getId())) {
            throw new LogicException(
                "Leveled directory id calculator requires numeric file ids ('{$obj->getId()}' was provided)"
            );
        }

        $directoryLevels = $this->getDirectoryLevels() + 1;
        $filesPerDirectory = $this->getFilesPerDirectory();

        $arr = array();
        $tmpfileid = $obj->getId() - 1;

        for ($count = 1; $count <= $directoryLevels; ++$count) {
            $lus = $tmpfileid / pow($filesPerDirectory, $directoryLevels - $count);
            $tmpfileid = $tmpfileid % pow($filesPerDirectory, $directoryLevels - $count);
            $arr[] = floor($lus) + 1;
        }

        array_pop($arr);

        return implode(DIRECTORY_SEPARATOR, $arr);
    }
}
