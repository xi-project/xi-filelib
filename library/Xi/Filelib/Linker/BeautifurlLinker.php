<?php

namespace Xi\Filelib\Linker;

use \Xi\Filelib\Linker\AbstractLinker,
    \Xi\Filelib\Linker\Linker,
    \Xi\Filelib\File\File,
    \Xi\Filelib\Folder\Folder,
    \Xi\Filelib\Plugin\VersionProvider\VersionProvider,
    \Xi\Filelib\Tool\Slugifier
    ;


/**
 * Creates beautifurls(tm) from the virtual directory structure and file names.
 *
 * @author pekkis
 *
 */
class BeautifurlLinker extends AbstractLinker implements Linker
{

    /**
     * @var boolean Exclude root folder from beautifurls or not
     */
    private $excludeRoot = false;

    private $slugifier;
    
    private $slugify = true;
        
    /**
     * Sets whether the root folder is excluded from beautifurls.
     *
     * @param boolean $excludeRoot
     */
    public function setExcludeRoot($excludeRoot)
    {
        $this->excludeRoot = $excludeRoot;
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
    
    public function getSlugifier()
    {
        if (!$this->slugifier) {
            $this->slugifier = new Slugifier();
        }
        return $this->slugifier;
    }

    
    public function setSlugify($slugify)
    {
        $this->slugify = $slugify;
    }
    
    
    public function getSlugify()
    {
        return $this->slugify;
    }
    


    public function getLinkVersion(File $file, VersionProvider $version)
    {
        $link = $this->getLink($file);
        $pinfo = pathinfo($link);
        $link = ($pinfo['dirname'] === '.' ? '' : $pinfo['dirname'] . '/') . $pinfo['filename'] . '-' . $version->getIdentifier();
        
        $link .= '.' . $version->getExtension();
        
        return $link;
    }


    public function getLink(File $file, $force = false)
    {
        
        if($force || !isset($file->link)) {
            	
            $folders = array();
            $folders[] = $folder = $this->getFilelib()->folder()->find($file->getFolderId());

            while($folder->getParentId()) {
                $folder = $this->getFilelib()->folder()->find($folder->getParentId());
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
           
            
                        
            $file->link = $beautifurl;

        }

        return $file->link;

    }

    
    


}
