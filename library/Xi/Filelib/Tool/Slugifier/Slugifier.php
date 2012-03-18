<?php

namespace Xi\Filelib\Tool\Slugifier;

/**
 * Slugifier interface
 */
interface Slugifier
{

    /**
     * Slugifies a path
     * 
     * @param string $path
     * @return string
     */
    public function slugifyPath($path);
    
    /**
     * Slugifies a word
     * 
     * @param string $unslugged
     * @return string
     */
    public function slugify($unslugged);
    
}
