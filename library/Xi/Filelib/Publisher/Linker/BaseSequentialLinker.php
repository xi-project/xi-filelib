<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Pekkis\DirectoryCalculator\DirectoryCalculator;
use Pekkis\DirectoryCalculator\Strategy\LeveledStrategy;
use Xi\Filelib\File\File;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Versionable\Version;

/**
 * Base class for sequential linkers
 *
 * @author pekkis
 */
abstract class BaseSequentialLinker
{
    /**
     * @var DirectoryCalculator
     */
    private $directoryCalculator;

    public function __construct($directoryLevels = 3, $filesPerDirectory = 500)
    {
        $this->directoryCalculator = new DirectoryCalculator(
            new LeveledStrategy($directoryLevels, $filesPerDirectory)
        );
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
        $url[] = $this->directoryCalculator->calculateDirectory($file);
        $name = $this->getFileName($file);
        $url[] = $name;
        $url = implode(DIRECTORY_SEPARATOR, $url);

        return $url;
    }

    /**
     * @param File $file
     * @return string
     */
    abstract protected function getFileName(File $file);
}
