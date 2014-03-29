<?php

namespace Xi\Filelib\Tests\Tool\Slugifier\Adapter;

use Xi\Filelib\Tool\Slugifier\Adapter\CocurSlugifierAdapter;

class CocurSlugifierTest extends TestCase
{
    public function setUp()
    {
        $this->slugifier = new CocurSlugifierAdapter();
    }
}
