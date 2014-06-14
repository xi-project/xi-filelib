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

/**
 * Calculates directory id by formatting an objects creation date
 */
class ReversibleCreationTimeLinker extends AbstractCreationTimeLinker implements ReversibleLinker
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
        $uuid = implode('-', $split);
        $file = $this->fileRepository->findByUuid($uuid);
        return array($file, $version);
    }
}
