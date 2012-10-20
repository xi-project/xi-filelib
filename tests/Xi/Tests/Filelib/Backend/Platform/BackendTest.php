<?php

namespace Xi\Tests\Filelib\Backend\Platform;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

/**
 * @group backend
 */
class BackendTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Backend\Platform\Backend'));
    }
}
