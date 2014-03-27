<?php

namespace Xi\Filelib\Tests\Cache\Adapter;

use Xi\Filelib\Tests\TestCase;

class CacheAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function exists()
    {
        $this->assertInterfaceExists('Xi\Filelib\Cache\Adapter\CacheAdapter');
    }
}
