<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;

/**
 * Interface for accelerated renderers
 * 
 * @author pekkis
 */
interface AcceleratedRenderer extends Renderer
{
    /**
     * Enables / disables acceleration
     * 
     * @param $enable boolean
     */
    public function enableAcceleration($enable);
    
    /**
     * Returns whether acceleration is enabled
     * 
     * @return boolean
     */
    public function isAccelerationEnabled();
    
    /**
     * Returns whether acceleration is possible
     * 
     * @return 
     */
    public function isAccelerationPossible();
    
}
