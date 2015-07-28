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
use Xi\Filelib\Versionable\Version;

/**
 * Sequential linker creates a sequential link with n levels of directories with m files per directory
 *
 * @author pekkis
 * @author Petri Mahanen
 */
class ReversibleSequentialLinker extends BaseSequentialLinker implements ReversibleLinker
{
    /**
     * @var FileRepository
     */
    protected $fileRepository;

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
}
