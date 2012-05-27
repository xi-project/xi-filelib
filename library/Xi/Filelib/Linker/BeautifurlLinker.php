<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Linker;

use Xi\Filelib\Linker\AbstractLinker;
use Xi\Filelib\Linker\Linker;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Tool\Slugifier\Slugifier;
use Xi\Filelib\Folder\FolderOperator;

/**
 * Creates beautifurls(tm) from the virtual directory structure and file names.
 *
 * @author pekkis
 */
class BeautifurlLinker extends AbstractLinker implements Linker
{
    /**
     * @var boolean Exclude root folder from beautifurls or not
     */
    private $excludeRoot = false;

    private $slugifierClass = 'Xi\Filelib\Tool\Slugifier\Zend2Slugifier';

    private $slugifier;

    private $slugify = true;

    /**
     * @var FolderOperator
     */
    private $folderOperator;

    /**
     * @param  FolderOperator   $folderOperator
     * @return BeautifurlLinker
     */
    public function __construct(FolderOperator $folderOperator)
    {
        $this->folderOperator = $folderOperator;
    }

    /**
     * Sets whether the root folder is excluded from beautifurls.
     *
     * @param boolean $excludeRoot
     * @return BeautifurlLinker
     */
    public function setExcludeRoot($excludeRoot)
    {
        $this->excludeRoot = $excludeRoot;
        return $this;
    }


    /**
     * Returns whether the root folder is to be excluded from beautifurls.
     *
     * @return unknown_type
     */
    public function getExcludeRoot()
    {
        return $this->excludeRoot;
    }

    /**
     * Returns slugifier class
     *
     * @return string
     */
    public function getSlugifierClass()
    {
        return $this->slugifierClass;
    }

    /**
     * Sets slugifier class
     *
     * @param type $slugifierClass
     * @return BeautifurlLinker
     */
    public function setSlugifierClass($slugifierClass)
    {
        $this->slugifierClass = $slugifierClass;
        return $this;
    }

    /**
     * Returns slugifier
     *
     * @return Slugifier
     */
    public function getSlugifier()
    {
        if (!$this->slugifier) {
            $className = $this->slugifierClass;
            $this->slugifier = new $className();
        }
        return $this->slugifier;
    }

    /**
     * Enables or disables slugifying
     *
     * @param boolean $slugify
     * @return BeautifurlLinker
     */
    public function setSlugify($slugify)
    {
        $this->slugify = $slugify;
        return $this;
    }

    /**
     * Returns whether slugifying is enabled
     *
     * @return boolean
     */
    public function getSlugify()
    {
        return $this->slugify;
    }


    public function getLinkVersion(File $file, $version, $extension)
    {
        $link = $this->getLink($file);
        $pinfo = pathinfo($link);
        $link = ($pinfo['dirname'] === '.' ? '' : $pinfo['dirname'] . '/') . $pinfo['filename'] . '-' . $version;

        $link .= '.' . $extension;

        return $link;
    }


    public function getLink(File $file)
    {
        $folders = array();
        $folders[] = $folder = $this->folderOperator->find($file->getFolderId());

        while($folder->getParentId()) {
            $folder = $this->folderOperator->find($folder->getParentId());
            array_unshift($folders, $folder);
        }

        $beautifurl = array();

        foreach($folders as $folder) {
            $beautifurl[] = $folder->getName();
        }


        if ($this->getSlugify()) {
            $slugifier = $this->getSlugifier();
            array_walk($beautifurl, function(&$frag) use ($slugifier) {
                $frag = $slugifier->slugify($frag);
            });
        }


        $beautifurl[] = $file->getName();

        if($this->getExcludeRoot()) {
            array_shift($beautifurl);
        }

        $beautifurl = implode(DIRECTORY_SEPARATOR, $beautifurl);

        return $beautifurl;

    }

}
