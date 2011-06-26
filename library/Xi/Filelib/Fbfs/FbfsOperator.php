<?php

namespace Xi\Filelib\Fbfs;

class FbfsOperator
{
    
    
    /**
     * Filelib reference
     * 
     * @var \Xi\Filelib\FileLibrary
     */
    protected $_filelib;
    
    public function __construct(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }
    
    
    public function register($protocol = 'fbfs', $host = 'filebank')
    {
        if (in_array($protocol, stream_get_wrappers())) {
            throw new \Xi\Filelib\FilelibException("Stream wrapper '{$protocol}' is already registered");
        }
        
        $registered = stream_wrapper_register($protocol, '\Xi\Filelib\Fbfs\StreamWrapper');
        
        if (!$registered) {
            throw new \Xi\Filelib\FilelibException("Stream wrapper '{$protocol}' failed to register");    
        }
        
        \Xi\Filelib\Fbfs\StreamWrapper::setFilelib($host, $this->_filelib);
                
    }
    
    
    
    
}