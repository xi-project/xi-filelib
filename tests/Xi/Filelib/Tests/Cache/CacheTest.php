<?php

namespace Xi\Filelib\Tests\Cache;

use Xi\Filelib\Tests\TestCase;

class CacheTest extends TestCase
{
    /**
     * @test
     */
    public function exists()
    {
        $this->assertInterfaceExists('Xi\Filelib\Cache\Cache');
    }
}
