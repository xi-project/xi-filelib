<?php

namespace Xi\Tests\Filelib\Backend\Finder;

use Xi\Tests\Filelib\TestCase;

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

