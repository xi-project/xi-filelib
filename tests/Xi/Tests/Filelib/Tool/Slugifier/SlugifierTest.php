<?php

namespace Xi\Tests\Filelib\Tool\Slugifier;

class SlugifierTest
{
 
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\Slugifier\Slugifier'));
    }
}
