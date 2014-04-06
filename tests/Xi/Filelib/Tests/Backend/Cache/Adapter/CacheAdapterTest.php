<?php

namespace Xi\Filelib\Tests\Backend\Cache\Adapter;

use Xi\Filelib\Tests\TestCase;

class CacheAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function exists()
    {
        $this->assertInterfaceExists('Xi\Filelib\Backend\Cache\Adapter\CacheAdapter');
    }
}
