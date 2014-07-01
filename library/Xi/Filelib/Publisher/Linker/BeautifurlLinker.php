<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Tool\Slugifier\Slugifier;
use Xi\Filelib\Version;

/**
 * Creates beautifurls(tm) from the virtual directory structure and file names.
 *
 * @author pekkis
 */
class BeautifurlLinker implements Linker
{
    /**
     * @var boolean Exclude root folder from beautifurls or not
     */
    private $excludeRoot = false;

    /**
     * @var Slugifier
     */
    private $slugifier;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @param FileLibrary $filelib
     * @param Slugifier $slugifier
     * @param bool $excludeRoot
     */
    public function __construct(Slugifier $slugifier = null, $excludeRoot = true)
    {
        $this->excludeRoot = $excludeRoot;
        $this->slugifier = $slugifier;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->folderRepository = $filelib->getFolderRepository();
    }


    /**
     * Returns whether the root folder is to be excluded from beautifurls.
     *
     * @return string
     */
    public function getExcludeRoot()
    {
        return $this->excludeRoot;
    }

    /**
     * Returns slugifier
     *
     * @return Slugifier
     */
    public function getSlugifier()
    {
        return $this->slugifier;
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
        $link = ($pinfo['dirname'] === '.'
                ? '' : $pinfo['dirname'] . '/') . $pinfo['filename'] . '-' . $version->toString();

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
        $folders = array();
        $folders[] = $folder = $this->folderRepository->find($file->getFolderId());

        while ($folder->getParentId()) {
            $folder = $this->folderRepository->find($folder->getParentId());
            array_unshift($folders, $folder);
        }

        $beautifurl = array();

        foreach ($folders as $folder) {
            $beautifurl[] = $folder->getName();
        }

        if ($slugifier = $this->getSlugifier()) {
            array_walk(
                $beautifurl,
                function (&$frag) use ($slugifier) {
                    $frag = $slugifier->slugify($frag);
                }
            );
        }

        $beautifurl[] = $file->getName();

        if ($this->getExcludeRoot()) {
            array_shift($beautifurl);
        }

        $beautifurl = implode(DIRECTORY_SEPARATOR, $beautifurl);

        return $beautifurl;
    }
}
