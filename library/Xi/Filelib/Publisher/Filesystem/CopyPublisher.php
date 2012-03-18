<?php

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

/**
 * Publishes files in a filesystem by retrieving them from storage and creating a copy
 * 
 * @author pekkis
 *
 */
class CopyPublisher extends AbstractFilesystemPublisher implements Publisher
{
    
    public function publish(File $file)
    {
        $fl = $this->getFilelib();
        $linker = $file->getProfileObject()->getLinker();
        
        $link = $this->getPublicRoot() . '/' . $linker->getLink($file, true);
        
        if(!is_file($link)) {

            $path = dirname($link);
            if(!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }

            $tmp = $this->getFilelib()->getStorage()->retrieve($file);

            copy($tmp, $link);
            chmod($link, $this->getFilePermission());            
            
        }
    }
    
    public function publishVersion(File $file, VersionProvider $version)
    {
        $fl = $this->getFilelib();
            
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        
                
        if(!is_file($link)) {

            $path = dirname($link);
            
            if(!is_dir($path)) {
                mkdir($path, $this->getDirectoryPermission(), true);
            }
            
            $tmp = $this->getFilelib()->getStorage()->retrieveVersion($file, $version->getIdentifier());
            copy($tmp, $link);
            chmod($link, $this->getFilePermission());            
        }
    }
    
    public function unpublish(File $file)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLink($file);
                        
        if(is_file($link)) {
            unlink($link);
        }
    }
    
    public function unpublishVersion(File $file, VersionProvider $version)
    {
        $link = $this->getPublicRoot() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        
        if(is_file($link)) {
            unlink($link);
        }
    }
    
}