<?php

namespace Xi\Filelib\Fbfs;

class StreamWrapper
{
    
    /**
     * Filelib references
     * 
     * @var \Xi\Filelib\FileLibrary
     */
    private static $filelibs = array();
    
    
    /**
     * Returns filelib
     * 
     * @return \Xi\Filelib\FileLibrary
     */
    public static function getFilelib($host)
    {
        return self::$filelibs[$host];
    }
    
    
    public static function setFilelib($host, \Xi\Filelib\FileLibrary $filelib)
    {
        self::$filelibs[$host] = $filelib;
    }

    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        echo "OPEN!!";

        return true;
    }
    
    
    public function stream_write($data)
    {
        
        return strlen($data) - 5;
    }
    
    
    public function stream_flush()
    {
        echo "FLLUSH!";
    }
    
    
    
    public function unlink($path)
    {
        $uri = parse_url($path);
        
        $filelib = self::getFilelib($uri['host']);
        
        $pinfo = pathinfo($uri['path']);
                
        $dirname = trim($pinfo['dirname'], '/');
                        
        $folder = $filelib->folder()->findByUrl($dirname);
        
        if(!$folder) {
            return false;
        }

        $file = $filelib->file()->findByFilename($folder, $pinfo['basename']);

        if (!$file) {
            return false;
        }
        
        $filelib->file()->delete($file);

        return true;
        
    }
    
    
    
    
}