<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\File\File;

interface Renderer
{
    public function render(File $file, array $options = array());
}
