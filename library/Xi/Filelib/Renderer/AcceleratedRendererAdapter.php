<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

/**
 * Interface for accelerated renderers
 *
 * @author pekkis
 */
interface AcceleratedRendererAdapter extends RendererAdapter
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
