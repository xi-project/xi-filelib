<?php

namespace Xi\Filelib\Tests\Publisher;

use Xi\Filelib\Tests\TestCase;

class PublisherAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertInterfaceExists('Xi\Filelib\Publisher\PublisherAdapter');
    }
}
