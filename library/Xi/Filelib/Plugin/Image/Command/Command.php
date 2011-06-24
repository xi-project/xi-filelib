<?php

namespace Xi\Filelib\Plugin\Image\Command;

use \Imagick;

/**
 * Interface for imagemagick version plugin commands
 * 
 * @author pekkis
 *
 */
interface Command
{
    

    public function execute(Imagick $img);
    
   
}