<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Renderer;

/**
 * Interface for renderers
 *
 * @author pekkis
 */
interface RendererAdapter
{
    /**
     * @param Response $response
     * @return mixed
     */
    public function adaptResponse(Response $response);
}
