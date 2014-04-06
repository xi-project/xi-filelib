<?php

namespace Xi\Filelib\Tests\Backend\Adapter;

use Xi\Filelib\Tests\TestCase as FilelibTestCase;

/**
 * @group backend
 */
class BackendAdapterTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Backend\Adapter\BackendAdapter'));
    }
}
