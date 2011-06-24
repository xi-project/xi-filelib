<?php

namespace Xi\Filelib\Publisher;

/**
 * Does absolutely nothing when files are published. Surprisingly it always succeeds and never returns anything
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class NothingPublisher extends AbstractPublisher
{
    
    public function publish(\Xi\Filelib\File\File $file)
    {
        
    }
        
    public function publishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        
    }
    
    public function unpublish(\Xi\Filelib\File\File $file)
    {
        
    }
    
    public function unpublishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        
    }
    
    
    public function getUrl(\Xi\Filelib\File\File $file)
    {
        return false;    
    }
    
    public function getUrlVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider $version)
    {
        return false;
    }
    
    
}