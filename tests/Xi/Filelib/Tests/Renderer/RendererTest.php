<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\File\File;
use Xi\Filelib\Renderer\Renderer;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;

class RendererTest extends RendererTestCase
{
    public function getAdapter()
    {
        return $this->getMock('Xi\Filelib\Renderer\Adapter\RendererAdapter');
    }

    public function getRenderer($adapter)
    {
        $renderer = new Renderer(
            $adapter
        );
        $renderer->attachTo($this->filelib);

        return $renderer;
    }
}
