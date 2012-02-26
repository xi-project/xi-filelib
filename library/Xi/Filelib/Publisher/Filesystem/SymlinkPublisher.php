<?php

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

/**
 * Publishes files in a filesystem by creating a symlink to the original file in the filesystem storage
 * 
 * @author pekkis
 *
 */
class SymlinkPublisher extends AbstractFilesystemPublisher implements Publisher
{
    
    /**
     * @var string Relative path from publisher root to storage root
     */
    private $relativePathToRoot;
    
    /**
     * Sets path from public to private root
     *
     * @param string $relativePathToRoot
     * @return SymlinkPublisher
     */
    public function setRelativePathToRoot($relativePathToRoot)
    {
        $this->relativePathToRoot = $relativePathToRoot;
    }

    /**
     * Returns path from public to private root
     *
     * @return string
     */
    public function getRelativePathToRoot()
    {
        return $this->relativePathToRoot;
    }
        
    /**
     * Returns relative path to file in storage
     *
     * @param File $file
     * @param $levelsDown How many levels down from root we are
     * @return string
     */
    public function getRelativePathTo(File $file, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();
        
        if(!$relativePath) {
            throw new FilelibException('Relative path must be set!');
        }
        $relativePath = str_repeat("../", $levelsDown) . $relativePath;
        
        $storage = $this->getFilelib()->getStorage();
        $retrieved = $storage->retrieve($file);
        
        $path = preg_replace("[^{$storage->getRoot()}]", $relativePath, $retrieved);
        
        return $path;
    }
    
    /**
     * Returns relative path to file version in storage
     *
     * @param File $file
     * @param VersionProvider $version
     * @param $levelsDown How many levels down from root we are
     * @return string
     */
    public function getRelativePathToVersion(File $file, VersionProvider $version, $levelsDown = 0)
    {
        $relativePath = $this->getRelativePathToRoot();
        
        if(!$relativePath) {
            throw new FilelibException('Relative path must be set!');
        }
        $relativePath = str_repeat("../", $levelsDown) . $relativePath;
        
        $storage = $this->getFilelib()->getStorage();
        $retrieved = $storage->retrieveVersion($file, $version->getIdentifier());
        
        $path = preg_replace("[^{$storage->getRoot()}]", $relativePath, $retrieved);
        
        return $path;
    }
    
    
    public function publish(File $file)
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
    
    public function publishVersion(File $file, VersionProvider $version)
    {
        $fl = $this->getFilelib();
            
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        
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
                
                $fp = $this->getRelativePathToVersion($file, $version, $depth);

                
                // Relative linking requires some movin'n groovin.
                $oldCwd = getcwd();
                chdir($path);
                symlink($fp, $link);
                chdir($oldCwd);
            } else {
                symlink($this->getFilelib()->getStorage()->retrieveVersion($file, $version->getIdentifier()), $link);                
            }

        }
        
        
    }
    
    public function unpublish(File $file)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLink($file);
        
        if(is_link($link)) {
            unlink($link);
        }
    }
    
    public function unpublishVersion(File $file, VersionProvider $version)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        if(is_link($link)) {
            unlink($link);
        }
    }
    
}