<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;

/**
 * Interface for renderers
 * 
 * @author pekkis
 */
interface Renderer
{
     /**
     * Returns url to a file
     * 
     * @param File $file
     * @param type $options
     * @return string 
     */
    public function getUrl(File $file, $options = array());
    
    /**
     * Renders a file to a response
     *
     * @param File $file File
     * @param array $options Render options
     * @return Response
     */
    public function render(File $file, array $options = array());
    
    
    
}
