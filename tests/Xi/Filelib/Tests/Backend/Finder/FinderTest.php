<?php

namespace Xi\Filelib\Tests\Backend\Finder;

use Xi\Filelib\Tests\TestCase;

class FinderTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Backend\Finder\Finder'));
    }
}
