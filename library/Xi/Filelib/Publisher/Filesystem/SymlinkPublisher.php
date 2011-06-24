<?php

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\Publisher\Publisher;

/**
 * Publishes files in a filesystem by creating a symlink to the original file in the filesystem storage
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class SymlinkPublisher extends AbstractFilesystemPublisher implements Publisher
{
    
    /**
     * @var string Relative path from public to private root
     */
    private $_relativePathToRoot;
    
    /**
     * Sets path from public to private root
     *
     * @param string $relativePathToRoot
     * @return \Xi\Filelib\FileLibrary
     */
    public function setRelativePathToRoot($relativePathToRoot)
    {
        $this->_relativePathToRoot = $relativePathToRoot;
        return $this;
    }

    /**
     * Returns path from public to private root
     *
     * @return string
     */
    public function getRelativePathToRoot()
    {
        return $this->_relativePathToRoot;
    }
        
    /**
     * Returns relative path from link to file in storage
     *
     * @param \Xi_Filelib_File $file File item
     * @param $levelsDown How many levels down from root
     * @return string
     */
    public function getRelativePathTo(\Xi\Filelib\File\File $file, $levelsDown = 0)
    {
        $sltr = $this->getRelativePathToRoot();
        
        if(!$sltr) {
            throw new \Xi\Filelib\FilelibException('Relative path must be set!');
        }
        $sltr = str_repeat("../", $levelsDown) . $sltr;
                
        $path = $this->getFilelib()->getStorage()->getRoot() . '/' . $this->getFilelib()->getStorage()->getDirectoryId($file) . '/' . $file->getId();
        
        $path = substr($path, strlen($this->getFilelib()->getStorage()->getRoot()));
        $sltr = $sltr . $path;
        
        return $sltr;
    }
    
    public function publish(\Xi\Filelib\File\File $file)
    {
        
        $fl = $this->getFilelib();
        $linker = $file->getProfileObject()->getLinker();
        
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file, true);
        
        if(!is_link($link)) {
            $path = dirname($link);
                
            if(!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }
                
            if($this->getRelativePathToRoot()) {

                $path2 = substr($path, strlen($this->getPublicRoot()) + 1);

                // If the link goes to the root dir, $path2 is false and fuxors the depth without a check.
                if($path2 === false) {
                    $depth = 0;
                } else {
                    $depth = sizeof(explode(DIRECTORY_SEPARATOR, $path2));
                }

                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                symlink($this->getRelativePathTo($file, $depth), $link);
                chdir($oldCwd);
            } else {
                symlink($this->getFilelib()->getStorage()->retrieve($file), $link);
            }
        }
        
    }
    
    public function publishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        $fl = $this->getFilelib();
            
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        
        if(!is_link($link)) {

            $path = dirname($link);
            if(!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            if($this->getRelativePathToRoot()) {

                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                    
                $path2 = substr($path, strlen($this->getPublicRoot()) + 1);
                    
                // If the link goes to the root dir, $path2 is false and fuxors the depth without a check.
                if($path2 === false) {
                    $depth = 0;
                } else {
                    $depth = sizeof(explode(DIRECTORY_SEPARATOR, $path2));
                }
                
                $fp = dirname($this->getRelativePathTo($file, $depth));
                $fp .= '/' . $version->getIdentifier() . '/' . $file->getId();
                
                symlink($fp, $link);
                    
                chdir($oldCwd);

            } else {
                symlink($this->getFilelib()->getStorage()->retrieveVersion($file, $version), $link);                
            }

        }
        
        
    }
    
    public function unpublish(\Xi\Filelib\File\File $file)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLink($file);
        if(is_link($link)) {
            unlink($link);
        }
    }
    
    public function unpublishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        if(is_link($link)) {
            unlink($link);
        }
    }
    
}