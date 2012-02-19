<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\FileProfile;

class FileProfileEvent extends Event
{
    /**
     * @var FileProfile
     */
    private $profile;
    
    public function __construct(FileProfile $profile)
    {
        $this->profile = $profile;
    }
    
    /**
     * Returns plugin
     * 
     * @return FileProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }
    
}
