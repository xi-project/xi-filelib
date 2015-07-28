<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer\Adapter;

/**
 * Interface for accelerated renderers
 *
 * @author pekkis
 */
interface AcceleratedRendererAdapter extends RendererAdapter
{
    public function canAccelerate();

    public function getServerSignature();
}
