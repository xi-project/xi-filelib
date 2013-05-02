<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * Renders a file to a response
     *
     * @param  File     $file    File
     * @param  array    $options Render options
     * @return Response
     */
    public function render(File $file, array $options = array());

}
