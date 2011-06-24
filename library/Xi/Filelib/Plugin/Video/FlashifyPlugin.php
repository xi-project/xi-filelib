<?php

namespace Xi\Filelib\Plugin\Video;


/**
 * Prototype proof-of-concept test plugin for video versioning
 *
 * @package Xi_Filelib
 * @author pekkis
 * @todo This is just a proof a concept
 *
 */
class FlashifyPlugin
extends \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider
{
    protected $_providesFor = array('video', 'application');

    public function createVersion(\Xi\Filelib\File\File $file)
    {

        $path = $file->getPath() . '/' . $this->getIdentifier();

        if(!is_dir($path)) {
            mkdir($path, $this->getFilelib()->getDirectoryPermission(), true);
        }
        	
        $exec_string = "/usr/bin/ffmpeg -i {$file->getPathname()} -f flv {$path}/{$file->id}";
         
        exec($exec_string); //where exxc is the command used to execute shell comma


    }


}
?>