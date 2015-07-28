<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Linker;

use Cocur\Slugify\Slugify;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Publisher\Linker;
use Xi\Filelib\Version;

/**
 * Creates beautifurls(tm) from the virtual directory structure and file names.
 *
 * @author pekkis
 */
class BeautifurlLinker implements Linker
{
    /**
     * @var Slugify
     */
    private $slugifier;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @param bool $excludeRoot
     */
    public function __construct()
    {
        $this->slugifier = Slugify::create();
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->folderRepository = $filelib->getFolderRepository();
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
        $folder = $this->folderRepository->find($file->getFolderId());
        $beautifurl = explode('/', $folder->getUrl());

        $beautifurl = array_filter(
            $beautifurl,
            function ($beautifurl) {
                return (bool) $beautifurl;
            }
        );

        $beautifurl = array_map(
            function ($fragment) {
                return $this->slugifier->slugify($fragment);
            },
            $beautifurl
        );

        $beautifurl[] = $file->getName();

        $beautifurl = implode(DIRECTORY_SEPARATOR, $beautifurl);

        return $beautifurl;
    }
}
